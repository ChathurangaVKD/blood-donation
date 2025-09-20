<?php
// session_check.php - Check user login status with enhanced session handling
// Configure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Lax');

session_start();
include 'db.php';

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

// Debug session information
error_log("BloodLink Session Check: Session ID: " . session_id());
error_log("BloodLink Session Check: Session data: " . print_r($_SESSION, true));

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_email'])) {
        // User is logged in
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'] ?? 1,
                'name' => $_SESSION['user_name'] ?? 'Unknown User',
                'email' => $_SESSION['user_email'],
                'blood_group' => $_SESSION['blood_group'] ?? 'Unknown'
            ],
            'session_id' => session_id()
        ]);
    } else {
        // User is not logged in
        echo json_encode([
            'success' => true,
            'logged_in' => false,
            'session_id' => session_id(),
            'debug' => [
                'session_exists' => !empty($_SESSION),
                'logged_in_set' => isset($_SESSION['logged_in']),
                'logged_in_value' => $_SESSION['logged_in'] ?? null,
                'user_email_set' => isset($_SESSION['user_email']),
                'session_data' => $_SESSION
            ]
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
