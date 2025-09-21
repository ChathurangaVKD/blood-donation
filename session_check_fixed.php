<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'success' => true,
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'] ?? 1,
            'name' => $_SESSION['user_name'] ?? 'Real User',
            'email' => $_SESSION['user_email'] ?? 'user@bloodlink.com',
            'blood_group' => $_SESSION['blood_group'] ?? 'O+',
            'location' => $_SESSION['location'] ?? 'Your City',
            'contact' => $_SESSION['contact'] ?? '123-456-7890'
        ]
    ]);
} else {
    echo json_encode(['success' => true, 'logged_in' => false]);
}
?>
