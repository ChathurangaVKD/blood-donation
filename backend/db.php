<?php
// db.php - Simplified for University Project
// Simple database connection for native PHP deployment

// University-friendly database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blood_donation";

// Try to connect to MySQL with MySQLi (existing connection)
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

// Create PDO connection for admin panel (needed for admin.php)
try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // If PDO fails, try to create database and reconnect
    try {
        $temp_pdo = new PDO("mysql:host=$servername;charset=utf8mb4", $username, $password);
        $temp_pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]);
        exit();
    }
}

// Initialize database tables if they don't exist
$tables_check = $conn->query("SHOW TABLES LIKE 'donors'");
if ($tables_check->num_rows == 0) {
    // Import database schema automatically
    $schema_file = __DIR__ . '/../database/schema.sql';
    if (file_exists($schema_file)) {
        $schema_sql = file_get_contents($schema_file);

        // Clean up SQL for execution
        $statements = array_filter(
            array_map('trim', explode(';', $schema_sql)),
            function($statement) {
                return !empty($statement) && !preg_match('/^(--|\#)/', $statement);
            }
        );

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $conn->query($statement);
                } catch (Exception $e) {
                    // Ignore table already exists errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        error_log("Database setup error: " . $e->getMessage());
                    }
                }
            }
        }
    }
}

// Utility function for input sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Success message for development
if (isset($_GET['test_db'])) {
    echo json_encode([
        'success' => true,
        'message' => 'Database connections established successfully',
        'mysqli' => $conn ? true : false,
        'pdo' => isset($pdo) ? true : false
    ]);
    exit();
}
?>