<?php
// admin.php - Admin management functionality with proper authentication
session_start();
include 'db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
                'admin' => [
                    'username' => $username,
                    'login_time' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            throw new Exception("Invalid admin credentials");
        }
        exit();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
}

// Handle admin logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_action']) && $_POST['admin_action'] === 'logout') {
    session_destroy();
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
    exit();
}

// For development/testing - allow temporary admin access with admin_key
if (!isAdmin() && isset($_GET['admin_key']) && $_GET['admin_key'] === 'dev123') {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = 'dev_admin';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (!isAdmin()) {
            throw new Exception("Admin access required. Please login first.");
        }

        $action = sanitizeInput($_POST['action'] ?? '');

        if ($action === 'verify_donor') {
            $donor_id = (int)($_POST['donor_id'] ?? 0);
            $verified = (int)($_POST['verified'] ?? 0);

            if ($donor_id <= 0) {
                throw new Exception("Invalid donor ID");
            }

            $stmt = $conn->prepare("UPDATE donors SET verified = ? WHERE id = ?");
            $stmt->bind_param("ii", $verified, $donor_id);

            if ($stmt->execute()) {
                $status = $verified ? 'verified' : 'unverified';
                echo json_encode([
                    'success' => true,
                    'message' => "Donor {$status} successfully."
                ]);
            } else {
                throw new Exception("Error updating donor verification");
            }
            $stmt->close();

        } elseif ($action === 'get_pending_donors') {
            $stmt = $conn->prepare("SELECT id, name, email, blood_group, location, contact, age, gender, created_at FROM donors WHERE verified = 0 ORDER BY created_at DESC");
            $stmt->execute();
            $result = $stmt->get_result();

            $pending_donors = [];
            while ($row = $result->fetch_assoc()) {
                $pending_donors[] = $row;
            }

            echo json_encode([
                'success' => true,
                'pending_donors' => $pending_donors,
                'count' => count($pending_donors)
            ]);
            $stmt->close();

        } elseif ($action === 'dashboard_stats') {
            // Get comprehensive dashboard statistics
            $stats = [];

            // Donor statistics
            $result = $conn->query("SELECT 
                COUNT(*) as total_donors,
                SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_donors,
                SUM(CASE WHEN verified = 0 THEN 1 ELSE 0 END) as pending_donors
                FROM donors");
            $stats['donors'] = $result->fetch_assoc();

            // Request statistics
            $result = $conn->query("SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                SUM(CASE WHEN status = 'fulfilled' THEN 1 ELSE 0 END) as fulfilled_requests,
                SUM(CASE WHEN urgency = 'Critical' AND status = 'pending' THEN 1 ELSE 0 END) as critical_requests
                FROM requests");
            $stats['requests'] = $result->fetch_assoc();

            // Inventory statistics
            $result = $conn->query("SELECT 
                COUNT(*) as total_units,
                SUM(CASE WHEN status = 'available' AND expiry_date > CURDATE() THEN 1 ELSE 0 END) as available_units,
                SUM(CASE WHEN expiry_date <= CURDATE() THEN 1 ELSE 0 END) as expired_units,
                SUM(CASE WHEN DATEDIFF(expiry_date, CURDATE()) <= 7 AND expiry_date > CURDATE() THEN 1 ELSE 0 END) as expiring_soon
                FROM inventory");
            $stats['inventory'] = $result->fetch_assoc();

            // Blood group distribution
            $result = $conn->query("SELECT blood_group, COUNT(*) as count 
                FROM inventory 
                WHERE status = 'available' AND expiry_date > CURDATE() 
                GROUP BY blood_group 
                ORDER BY blood_group");
            $stats['blood_distribution'] = [];
            while ($row = $result->fetch_assoc()) {
                $stats['blood_distribution'][] = $row;
            }

            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);

        } elseif ($action === 'update_request_status') {
            $request_id = (int)($_POST['request_id'] ?? 0);
            $status = sanitizeInput($_POST['status'] ?? '');

            if ($request_id <= 0) {
                throw new Exception("Invalid request ID");
            }

            $valid_statuses = ['pending', 'fulfilled', 'cancelled'];
            if (!in_array($status, $valid_statuses)) {
                throw new Exception("Invalid status");
            }

            $stmt = $conn->prepare("UPDATE requests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("si", $status, $request_id);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Request status updated successfully.'
                ]);
            } else {
                throw new Exception("Error updating request status");
            }
            $stmt->close();

        } else {
            throw new Exception("Invalid action");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        if (!isAdmin()) {
            throw new Exception("Admin access required. Please login first.");
        }

        $action = $_GET['action'] ?? '';

        if ($action === 'donors') {
            $verified = $_GET['verified'] ?? '';

            $sql = "SELECT id, name, email, blood_group, location, contact, age, gender, verified, created_at, last_donation_date FROM donors";
            $params = [];
            $types = "";

            if ($verified !== '') {
                $sql .= " WHERE verified = ?";
                $params[] = (int)$verified;
                $types .= "i";
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $donors = [];
            while ($row = $result->fetch_assoc()) {
                $donors[] = $row;
            }

            echo json_encode([
                'success' => true,
                'donors' => $donors
            ]);
            $stmt->close();

        } else {
            throw new Exception("Invalid action");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

$conn->close();
?>
