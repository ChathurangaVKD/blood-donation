<?php
// auto_db.php - Automated database configuration for one-time setup
$servername = "localhost";
$username = "blooddonation";
$password = "blooddonation123";
$dbname = "blood_donation";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Fallback to root user if blooddonation user doesn't exist yet
    $conn = new mysqli("localhost", "root", "", $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

$conn->set_charset("utf8mb4");

// ...existing code...
