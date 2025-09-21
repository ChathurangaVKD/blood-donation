<?php
// simple_login.php - Direct login for your user account
session_start();

// Set session variables with your actual user data
$_SESSION['user_id'] = 2017;
$_SESSION['user_name'] = 'Dasun Chathuranga';
$_SESSION['user_email'] = 'vkdchathuranga@gmail.com';
$_SESSION['blood_group'] = 'A-';
$_SESSION['location'] = 'Colombo District, Western Province, Sri Lanka';
$_SESSION['contact'] = '+94719436366';
$_SESSION['logged_in'] = true;
$_SESSION['login_time'] = time();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Login successful! Session established.',
    'user' => [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'blood_group' => $_SESSION['blood_group'],
        'location' => $_SESSION['location'],
        'contact' => $_SESSION['contact']
    ],
    'session_id' => session_id(),
    'redirect' => 'monitor.html'
]);
?>
