<?php
// direct_session_check.php - Direct session check without routing issues
session_start();

// Set JSON header immediately
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Simple session check
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_email'])) {
    // User is logged in
    echo json_encode([
        'success' => true,
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'] ?? 1,
            'name' => $_SESSION['user_name'] ?? 'Test User',
            'email' => $_SESSION['user_email'] ?? 'test@bloodlink.com',
            'blood_group' => $_SESSION['blood_group'] ?? 'O+'
        ],
        'session_id' => session_id()
    ]);
} else {
    // User is not logged in
    echo json_encode([
        'success' => true,
        'logged_in' => false,
        'session_id' => session_id()
    ]);
}
?>
