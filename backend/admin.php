<?php
// admin.php - Admin management functionality with proper authentication
session_start();
include 'db.php';

// Set proper headers for CORS and sessions
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

// Admin credentials (in production, store these in database with proper hashing)
$admin_credentials = [
    'admin' => 'admin123',
    'manager' => 'admin123',
    'root' => 'root123'
];

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Admin login function
function adminLogin($username, $password) {
    global $admin_credentials;

    if (isset($admin_credentials[$username]) && $admin_credentials[$username] === $password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_login_time'] = time();
        return true;
    }
    return false;
}

// Handle admin login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_action']) && $_POST['admin_action'] === 'login') {
    try {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            throw new Exception("Username and password are required");
        }

        if (adminLogin($username, $password)) {
            echo json_encode([
                'success' => true,
                'message' => 'Admin login successful',
                'admin_username' => $username,
                'session_id' => session_id()
            ]);
        } else {
            throw new Exception("Invalid admin credentials");
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Handle admin logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_action']) && $_POST['admin_action'] === 'logout') {
    session_destroy();
    echo json_encode([
        'success' => true,
        'message' => 'Admin logout successful'
    ]);
    exit();
}

// Handle session check
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] === 'check_session') {
    echo json_encode([
        'success' => true,
        'logged_in' => isAdmin(),
        'admin_username' => $_SESSION['admin_username'] ?? null,
        'session_id' => session_id()
    ]);
    exit();
}

// Check authentication for protected routes (skip for login and session check)
$is_login_request = isset($_POST['admin_action']) && $_POST['admin_action'] === 'login';
$is_session_check = isset($_GET['action']) && $_GET['action'] === 'check_session';

if (!$is_login_request && !$is_session_check) {
    if (!isAdmin()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Admin authentication required',
            'session_id' => session_id(),
            'session_data' => $_SESSION
        ]);
        exit();
    }
}

// Handle GET requests - List blood requests
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $action = $_GET['action'] ?? 'list_requests';

        switch ($action) {
            case 'list_requests':
                // Get filter parameters
                $status = $_GET['status'] ?? '';
                $urgency = $_GET['urgency'] ?? '';
                $blood_group = $_GET['blood_group'] ?? '';
                $limit = min((int)($_GET['limit'] ?? 50), 100); // Max 100 records
                $offset = max((int)($_GET['offset'] ?? 0), 0);

                // Build query with filters
                $where_conditions = [];
                $params = [];

                if (!empty($status)) {
                    $where_conditions[] = "status = ?";
                    $params[] = $status;
                }

                if (!empty($urgency)) {
                    $where_conditions[] = "urgency = ?";
                    $params[] = $urgency;
                }

                if (!empty($blood_group)) {
                    $where_conditions[] = "blood_group = ?";
                    $params[] = $blood_group;
                }

                $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

                // Get total count
                $count_sql = "SELECT COUNT(*) as total FROM requests $where_clause";
                $count_stmt = $pdo->prepare($count_sql);
                $count_stmt->execute($params);
                $total_count = $count_stmt->fetch()['total'];

                // Get requests with pagination
                $sql = "SELECT id, requester_name, requester_contact, requester_email, blood_group, 
                               location, urgency, hospital, required_date, units_needed, status, 
                               notes, created_at, updated_at 
                        FROM requests $where_clause 
                        ORDER BY created_at DESC, urgency DESC 
                        LIMIT ? OFFSET ?";

                $params[] = $limit;
                $params[] = $offset;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $requests = $stmt->fetchAll();

                echo json_encode([
                    'success' => true,
                    'data' => $requests,
                    'pagination' => [
                        'total' => $total_count,
                        'limit' => $limit,
                        'offset' => $offset,
                        'has_more' => ($offset + $limit) < $total_count
                    ]
                ]);
                break;

            case 'stats':
                // Get dashboard statistics
                $stats_sql = "
                    SELECT 
                        COUNT(*) as total_requests,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                        SUM(CASE WHEN status = 'fulfilled' THEN 1 ELSE 0 END) as fulfilled_requests,
                        SUM(CASE WHEN urgency = 'Critical' THEN 1 ELSE 0 END) as critical_requests,
                        SUM(CASE WHEN urgency = 'High' THEN 1 ELSE 0 END) as high_requests,
                        SUM(units_needed) as total_units_requested
                    FROM requests
                ";

                $stats_stmt = $pdo->prepare($stats_sql);
                $stats_stmt->execute();
                $stats = $stats_stmt->fetch();

                echo json_encode([
                    'success' => true,
                    'stats' => $stats
                ]);
                break;

            default:
                throw new Exception("Invalid action");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Handle PUT requests - Update blood request status
if ($_SERVER["REQUEST_METHOD"] == "PUT" || ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['_method']) && $_POST['_method'] === 'PUT')) {
    try {
        // Get PUT data
        if ($_SERVER["REQUEST_METHOD"] == "PUT") {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $request_id = (int)($input['id'] ?? 0);
        $new_status = sanitizeInput($input['status'] ?? '');
        $admin_notes = sanitizeInput($input['admin_notes'] ?? '');

        if ($request_id <= 0) {
            throw new Exception("Invalid request ID");
        }

        if (!in_array($new_status, ['pending', 'fulfilled', 'cancelled'])) {
            throw new Exception("Invalid status");
        }

        // Update the request
        $update_sql = "UPDATE requests SET status = ?, notes = CONCAT(COALESCE(notes, ''), '\n[Admin Update by ', ?, ']: ', ?) WHERE id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$new_status, $_SESSION['admin_username'], $admin_notes, $request_id]);

        if ($update_stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Request status updated successfully',
                'request_id' => $request_id,
                'new_status' => $new_status
            ]);
        } else {
            throw new Exception("Request not found or no changes made");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Handle DELETE requests - Delete blood request
if ($_SERVER["REQUEST_METHOD"] == "DELETE" || ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['_method']) && $_POST['_method'] === 'DELETE')) {
    try {
        // Get DELETE data
        if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
            $input = json_decode(file_get_contents('php://input'), true);
            $request_id = (int)($input['id'] ?? 0);
        } else {
            $request_id = (int)($_POST['id'] ?? 0);
        }

        if ($request_id <= 0) {
            throw new Exception("Invalid request ID");
        }

        // Delete the request
        $delete_sql = "DELETE FROM requests WHERE id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$request_id]);

        if ($delete_stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Request deleted successfully',
                'request_id' => $request_id
            ]);
        } else {
            throw new Exception("Request not found");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Invalid request method
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Method not allowed'
]);
?>
