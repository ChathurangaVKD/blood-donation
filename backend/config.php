<?php
// University Demo Configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blood_donation";

// Simple connection for demo
$conn = @new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // Try to create database if it doesn't exist
    $temp_conn = @new mysqli($servername, $username, $password);
    if (!$temp_conn->connect_error) {
        $temp_conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
        $temp_conn->close();
        $conn = new mysqli($servername, $username, $password, $dbname);
    }
}

if ($conn->connect_error) {
    die("Demo Setup Error: Please ensure MySQL is running or use XAMPP");
}

$conn->set_charset("utf8mb4");

// Simple university project functions
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

if (!function_exists('hashPassword')) {
    function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
