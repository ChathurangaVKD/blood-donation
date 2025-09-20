<?php
// fix_medical_history.php - Database migration to add missing medical_history column
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Blood Donation Database Fix</h2>";

try {
    // Connect to MySQL
    $conn = new mysqli('localhost', 'root', '', '');
    if ($conn->connect_error) {
        throw new Exception('Cannot connect to MySQL: ' . $conn->connect_error);
    }

    echo "<p>✓ Connected to MySQL</p>";

    // Create database if it doesn't exist
    $conn->query('CREATE DATABASE IF NOT EXISTS blood_donation');
    $conn->select_db('blood_donation');
    echo "<p>✓ Using blood_donation database</p>";

    // Check if donors table exists
    $result = $conn->query('SHOW TABLES LIKE "donors"');
    if ($result->num_rows == 0) {
        echo "<p>⚠️ Donors table does not exist. Creating complete table...</p>";
        // Create the full table with medical_history column
        $sql = "
        CREATE TABLE donors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            age INT NOT NULL,
            gender ENUM('Male', 'Female', 'Other') NOT NULL,
            blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
            contact VARCHAR(15) NOT NULL,
            location VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            medical_history TEXT NULL,
            verified BOOLEAN DEFAULT FALSE,
            last_donation_date DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        if ($conn->query($sql)) {
            echo "<p>✓ Successfully created donors table with medical_history column!</p>";
        } else {
            throw new Exception('Error creating table: ' . $conn->error);
        }
    } else {
        echo "<p>✓ Donors table exists. Checking for medical_history column...</p>";
        $result = $conn->query('SHOW COLUMNS FROM donors LIKE "medical_history"');
        if ($result->num_rows == 0) {
            echo "<p>⚠️ Adding missing medical_history column...</p>";
            $sql = 'ALTER TABLE donors ADD COLUMN medical_history TEXT NULL AFTER password';
            if ($conn->query($sql)) {
                echo "<p>✓ Successfully added medical_history column!</p>";
            } else {
                throw new Exception('Error adding column: ' . $conn->error);
            }
        } else {
            echo "<p>✓ medical_history column already exists!</p>";
        }
    }

    // Create other tables if they don't exist
    $tables = [
        'requests' => "
        CREATE TABLE IF NOT EXISTS requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            requester_name VARCHAR(100) NOT NULL,
            requester_contact VARCHAR(15) NOT NULL,
            requester_email VARCHAR(100) NOT NULL,
            blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
            location VARCHAR(100) NOT NULL,
            urgency ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
            hospital VARCHAR(100) NOT NULL,
            required_date DATE NOT NULL,
            units_needed INT DEFAULT 1,
            status ENUM('pending', 'fulfilled', 'cancelled') DEFAULT 'pending',
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'inventory' => "
        CREATE TABLE IF NOT EXISTS inventory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
            donor_id INT NULL,
            collection_date DATE NOT NULL,
            expiry_date DATE NOT NULL,
            status ENUM('available', 'reserved', 'used', 'expired') DEFAULT 'available',
            location VARCHAR(100) NOT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach ($tables as $table_name => $create_sql) {
        if ($conn->query($create_sql)) {
            echo "<p>✓ Ensured $table_name table exists</p>";
        } else {
            echo "<p>⚠️ Warning creating $table_name table: " . $conn->error . "</p>";
        }
    }

    // Show final table structure
    echo "<h3>Final donors table structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    $result = $conn->query('DESCRIBE donors');
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h3>✅ Database Fix Complete!</h3>";
    echo "<p>You can now try registering again. The medical_history column should work properly.</p>";
    echo "<p><a href='../frontend/register.html'>Test Registration</a> | <a href='../frontend/index.html'>Go to App</a></p>";

    $conn->close();

} catch (Exception $e) {
    echo "<div style='background: #fee; border: 2px solid #f00; padding: 20px; margin: 20px; border-radius: 10px;'>";
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p><strong>Possible solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure MySQL is running (try starting XAMPP)</li>";
    echo "<li>Check if the MySQL service is started</li>";
    echo "<li>Verify MySQL credentials (localhost, root, no password)</li>";
    echo "</ul>";
    echo "</div>";
}
?>
