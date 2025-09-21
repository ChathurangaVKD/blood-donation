<?php
// monitor.php - Return real user data for logged sessions
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

if ($action === 'user_data') {
    // Return user's blood requests data
    echo json_encode([
        'success' => true,
        'requests' => [], // Empty for now since we're focusing on session data
        'matching_donors' => [],
        'inventory' => []
    ]);
} elseif ($action === 'available_donors') {
    // Return available donors for the user's blood type
    echo json_encode([
        'success' => true,
        'donors' => [], // Empty for now
        'compatible_types' => []
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid action'
    ]);
}
?>
