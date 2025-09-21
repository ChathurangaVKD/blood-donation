<?php
// session_manager.php - Robust session management with persistent storage
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600); // 1 hour session lifetime

// Start session with custom settings
session_start();

include 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function getUserFromSession() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return null;
    }

    // Return user data from session
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'blood_group' => $_SESSION['blood_group'] ?? '',
        'location' => $_SESSION['location'] ?? '',
        'contact' => $_SESSION['contact'] ?? ''
    ];
}

function refreshUserFromDatabase($userId) {
    global $conn;

    $stmt = $conn->prepare("SELECT id, name, email, blood_group, location, contact FROM donors WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Update session with fresh data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['blood_group'] = $user['blood_group'];
        $_SESSION['location'] = $user['location'];
        $_SESSION['contact'] = $user['contact'];
        $_SESSION['logged_in'] = true;

        return $user;
    }

    return null;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        error_log("Session Manager: Session ID: " . session_id());
        error_log("Session Manager: Session data: " . print_r($_SESSION, true));

        $user = getUserFromSession();

        if ($user && $user['id']) {
            // Refresh user data from database to ensure it's current
            $freshUser = refreshUserFromDatabase($user['id']);

            if ($freshUser) {
                echo json_encode([
                    'success' => true,
                    'logged_in' => true,
                    'user' => $freshUser,
                    'session_id' => session_id(),
                    'timestamp' => time()
                ]);
            } else {
                // User not found in database, clear session
                session_destroy();
                echo json_encode([
                    'success' => true,
                    'logged_in' => false,
                    'message' => 'User not found, session cleared'
                ]);
            }
        } else {
            echo json_encode([
                'success' => true,
                'logged_in' => false,
                'session_id' => session_id()
            ]);
        }

    } catch (Exception $e) {
        error_log("Session Manager Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'logged_in' => false,
            'error' => $e->getMessage()
        ]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'logout') {
        session_destroy();
        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
?>
