<?php
// db.php - Simplified for University Project
// Simple database connection for native PHP deployment

// University-friendly database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blood_donation";

// Try to connect to MySQL
$conn = @new mysqli($servername, $username, $password, $dbname);

// If database doesn't exist, create it
if ($conn->connect_error && strpos($conn->connect_error, 'Unknown database') !== false) {
    $temp_conn = @new mysqli($servername, $username, $password);
    if (!$temp_conn->connect_error) {
        $temp_conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
        $temp_conn->close();
        $conn = @new mysqli($servername, $username, $password, $dbname);
    }
}

// If still failing, provide helpful university-friendly error
if ($conn->connect_error) {
    echo "<div style='background: #fee; border: 2px solid #f00; padding: 20px; margin: 20px; border-radius: 10px;'>";
    echo "<h2>🎓 University Project Database Setup Required</h2>";
    echo "<p><strong>Error:</strong> " . $conn->connect_error . "</p>";
    echo "<h3>Quick Fix for University Demo:</h3>";
    echo "<ol>";
    echo "<li><strong>Install XAMPP:</strong> Download from <a href='https://www.apachefriends.org/'>apachefriends.org</a></li>";
    echo "<li><strong>Start XAMPP:</strong> Open XAMPP Control Panel and start Apache + MySQL</li>";
    echo "<li><strong>Run Setup:</strong> Execute <code>setup_xampp.bat</code> or <code>./setup_xampp.sh</code></li>";
    echo "<li><strong>Alternative:</strong> Ensure MySQL is running on localhost with root user (no password)</li>";
    echo "</ol>";
    echo "<p><em>This is normal for university projects - MySQL needs to be running first!</em></p>";
    echo "</div>";
    exit();
}

$conn->set_charset("utf8mb4");

// Initialize database tables if they don't exist
$tables_check = $conn->query("SHOW TABLES LIKE 'donors'");
if ($tables_check->num_rows == 0) {
    // Import database schema automatically
    $schema_file = __DIR__ . '/../database/schema.sql';
    if (file_exists($schema_file)) {
        $schema_sql = file_get_contents($schema_file);
        if ($conn->multi_query($schema_sql)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->next_result());
        }

        // Import sample data
        $sample_file = __DIR__ . '/../database/sample_data.sql';
        if (file_exists($sample_file)) {
            $sample_sql = file_get_contents($sample_file);
            if ($conn->multi_query($sample_sql)) {
                do {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->next_result());
            }
        }
    }
}

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

function verifyPassword($password, $hash) {
    // Handle both hashed and plain passwords for university demo
    if (strlen($hash) > 20) {
        return password_verify($password, $hash);
    }
    return $password === $hash;
}

// Success indicator for university demo
if (!headers_sent()) {
    header('X-University-Demo: Ready');
}
?>