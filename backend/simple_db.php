<?php
// simple_db.php - Simplified database configuration for local development
$servername = "localhost";
$username = "root";
$password = ""; // Change this if you have a MySQL password
$dbname = "blood_donation";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Helper functions
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>
