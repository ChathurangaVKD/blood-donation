<?php
// db.php - Updated for Docker container setup
$servername = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : "mysql";
$username = isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : "blooddonation";
$password = isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : "blooddonation123";
$dbname = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : "blood_donation";

// Try blooddonation user first, fallback to root
$conn = @new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    $conn = @new mysqli($servername, "root", "blooddonation123", $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

$conn->set_charset("utf8mb4");

// Enhanced database functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateToken($length = 32) {
    // PHP 5.6+ compatible random token generation
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    } else {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}

// Database initialization function
function initializeDatabase() {
    global $conn;

    $sql = file_get_contents(__DIR__ . '/../database/schema.sql');

    if ($sql === false) {
        throw new RuntimeException("Could not read schema file");
    }

    // Execute multiple statements
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
    } else {
        throw new RuntimeException("Error initializing database: " . $conn->error);
    }
}

// Check if database tables exist
function checkDatabaseSetup() {
    global $conn;

    $tables = array('donors', 'requests', 'inventory', 'admins', 'donations', 'request_fulfillments');
    $existing_tables = array();

    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $existing_tables[] = $row[0];
    }

    foreach ($tables as $table) {
        if (!in_array($table, $existing_tables)) {
            return false;
        }
    }

    return true;
}

// Auto-initialize database if tables don't exist
if (!checkDatabaseSetup()) {
    try {
        initializeDatabase();
        error_log("Database initialized successfully");
    } catch (Exception $e) {
        error_log("Database initialization failed: " . $e->getMessage());
    }
}
?>