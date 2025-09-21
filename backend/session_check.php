<?php
// session_check.php - Extract real user data from logged sessions
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

// Check session status and return user data
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_email'])) {
    // User is logged in, return session data
    echo json_encode([
        'success' => true,
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? 'Name not available',
            'email' => $_SESSION['user_email'] ?? 'Email not available',
            'blood_group' => $_SESSION['blood_group'] ?? 'Not specified',
            'location' => $_SESSION['location'] ?? 'Not specified',
            'contact' => $_SESSION['contact'] ?? 'Not provided'
        ],
        'session_id' => session_id()
    ]);
} else {
    // User is not logged in
    echo json_encode([
        'success' => true,
        'logged_in' => false,
        'user' => null,
        'message' => 'No active session found'
    ]);
}
?>
