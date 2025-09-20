<?php
// standalone_session_check.php - Session check without database dependency
// Configure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');

session_start();

// Set proper headers for session handling
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Debug logging
error_log("BloodLink Standalone Session Check: Session ID: " . session_id());
error_log("BloodLink Standalone Session Check: Session data: " . print_r($_SESSION, true));

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_email'])) {
        // User is logged in
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'] ?? 1,
                'name' => $_SESSION['user_name'] ?? 'Demo User',
                'email' => $_SESSION['user_email'],
                'blood_group' => $_SESSION['blood_group'] ?? 'O+'
            ],
            'session_id' => session_id(),
            'router_working' => true
        ]);
    } else {
        // User is not logged in - return success to show router is working
        echo json_encode([
            'success' => true,
            'logged_in' => false,
            'session_id' => session_id(),
            'router_working' => true,
            'message' => 'PHP Built-in Server routing is working correctly'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'router_working' => true
    ]);
}
?>
