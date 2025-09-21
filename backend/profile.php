<?php
// profile.php - Return real user profile data from session
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_email'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Not logged in'
    ]);
    exit();
}

$action = $_GET['action'] ?? '';

if ($action === 'get_profile') {
    // Return real user profile data from session
    echo json_encode([
        'success' => true,
        'profile' => [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'blood_group' => $_SESSION['blood_group'],
            'location' => $_SESSION['location'] ?? 'Not specified',
            'contact' => $_SESSION['contact'] ?? 'Not provided',
            'age' => $_SESSION['age'] ?? 'Not specified',
            'gender' => $_SESSION['gender'] ?? 'Not specified',
            'created_at' => date('Y-m-d'),
            'last_donation_date' => null
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid action'
    ]);
}
?>
