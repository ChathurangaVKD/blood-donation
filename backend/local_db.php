<?php
// local_db.php - Local development database configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Local database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Empty for local development
$dbname = "blood_donation";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($dbname);

// Set charset
$conn->set_charset("utf8mb4");

// Create tables
$tables_sql = "
-- Donors table
CREATE TABLE IF NOT EXISTS donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    contact VARCHAR(15) NOT NULL,
    location VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    last_donation_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blood requests table
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
);

-- Blood inventory table
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
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

// Execute table creation
if ($conn->multi_query($tables_sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "Tables created successfully<br>";
} else {
    echo "Error creating tables: " . $conn->error . "<br>";
}

// Insert sample data
$sample_data = "
INSERT IGNORE INTO donors (name, age, gender, blood_group, contact, location, email, password, verified) VALUES 
('John Doe', 28, 'Male', 'O+', '+1-555-0101', 'New York', 'john.doe@email.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Jane Smith', 32, 'Female', 'A+', '+1-555-0102', 'Los Angeles', 'jane.smith@email.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Mike Johnson', 25, 'Male', 'B-', '+1-555-0103', 'Chicago', 'mike.johnson@email.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

INSERT IGNORE INTO inventory (blood_group, collection_date, expiry_date, location, status) VALUES 
('O+', '2024-09-01', '2024-10-13', 'New York Blood Center', 'available'),
('A+', '2024-09-05', '2024-10-17', 'LA Medical Center', 'available'),
('B-', '2024-09-10', '2024-10-22', 'Chicago General Hospital', 'available');

INSERT IGNORE INTO requests (requester_name, requester_contact, requester_email, blood_group, location, urgency, hospital, required_date, units_needed) VALUES 
('Dr. Emergency', '+1-911-0001', 'emergency@hospital.com', 'O-', 'New York', 'Critical', 'NYC Emergency Hospital', '2024-09-25', 2),
('Dr. Surgery', '+1-555-1001', 'surgery@medical.com', 'A+', 'Los Angeles', 'High', 'LA Medical Center', '2024-09-28', 1);
";

if ($conn->multi_query($sample_data)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "Sample data inserted successfully<br>";
} else {
    echo "Error inserting sample data: " . $conn->error . "<br>";
}

echo "<h2>Database Setup Complete!</h2>";
echo "<p>You can now use the Blood Donation System with local MySQL.</p>";
echo "<p><a href='../frontend/index.html'>Go to Application</a></p>";

// Database functions
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
    return password_verify($password, $hash);
}
?>
