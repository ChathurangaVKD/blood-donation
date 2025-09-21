<?php
// working_session_check.php - Direct session check that works without routing issues
session_start();

// Force JSON output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');

// Simple session check - extract real user data
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_email'])) {
    // User is logged in - return REAL user data from session
    echo json_encode([
        'success' => true,
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'] ?? 1,
            'name' => $_SESSION['user_name'] ?? 'Unknown User',
            'email' => $_SESSION['user_email'],
            'blood_group' => $_SESSION['blood_group'] ?? 'Unknown',
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
        'session_id' => session_id()
    ]);
}
?>
