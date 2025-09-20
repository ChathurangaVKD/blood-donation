#!/usr/bin/env php
<?php
// setup_database.php - Database setup and initialization script
require_once __DIR__ . '/config.php';

class DatabaseSetup {
    private $host;
    private $username;
    private $password;
    private $dbname;

    public function __construct() {
        $this->host = Config::DB_HOST;
        $this->username = Config::DB_USERNAME;
        $this->password = Config::DB_PASSWORD;
        $this->dbname = Config::DB_NAME;
    }

    public function run() {
        echo "Blood Donation System - Database Setup\n";
        echo "=====================================\n\n";

        try {
            // Step 1: Check MySQL connection
            echo "1. Testing MySQL connection...\n";
            $this->testConnection();
            echo "   ✓ MySQL connection successful\n\n";

            // Step 2: Create database if it doesn't exist
            echo "2. Creating database if needed...\n";
            $this->createDatabase();
            echo "   ✓ Database created/verified\n\n";

            // Step 3: Create tables
            echo "3. Creating tables...\n";
            $this->createTables();
            echo "   ✓ Tables created successfully\n\n";

            // Step 4: Insert sample data
            echo "4. Inserting sample data...\n";
            $this->insertSampleData();
            echo "   ✓ Sample data inserted\n\n";

            echo "Database setup completed successfully!\n";
            echo "You can now use the Blood Donation System.\n\n";
            echo "Default admin credentials:\n";
            echo "Username: admin\n";
            echo "Password: admin123\n";
            echo "(Please change these in production!)\n";

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function testConnection() {
        $conn = new mysqli($this->host, $this->username, $this->password);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->close();
    }

    private function createDatabase() {
        $conn = new mysqli($this->host, $this->username, $this->password);
        $sql = "CREATE DATABASE IF NOT EXISTS `{$this->dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (!$conn->query($sql)) {
            throw new Exception("Error creating database: " . $conn->error);
        }
        $conn->close();
    }

    private function createTables() {
        $conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        if ($schema === false) {
            throw new Exception("Could not read schema file");
        }

        // Execute multiple statements
        if ($conn->multi_query($schema)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->next_result());
        } else {
            throw new Exception("Error creating tables: " . $conn->error);
        }

        $conn->close();
    }

    private function insertSampleData() {
        $conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        // Sample donors
        $donors = [
            ['John Doe', 25, 'Male', 'O+', '1234567890', 'New York', 'john@example.com'],
            ['Jane Smith', 30, 'Female', 'A+', '0987654321', 'Los Angeles', 'jane@example.com'],
            ['Mike Johnson', 28, 'Male', 'B-', '5555555555', 'Chicago', 'mike@example.com'],
            ['Sarah Wilson', 35, 'Female', 'AB+', '7777777777', 'Houston', 'sarah@example.com']
        ];

        foreach ($donors as $donor) {
            $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO donors (name, age, gender, blood_group, contact, location, email, password, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("sissssss", $donor[0], $donor[1], $donor[2], $donor[3], $donor[4], $donor[5], $donor[6], $hashed_password);
            $stmt->execute();
        }

        // Sample inventory
        $inventory_items = [
            ['O+', '2024-01-15', '2024-02-26', 'New York Hospital'],
            ['A+', '2024-01-16', '2024-02-27', 'Los Angeles Medical Center'],
            ['B-', '2024-01-17', '2024-02-28', 'Chicago General Hospital'],
            ['AB+', '2024-01-18', '2024-03-01', 'Houston Medical Center'],
            ['O-', '2024-01-19', '2024-03-02', 'New York Hospital']
        ];

        foreach ($inventory_items as $item) {
            $stmt = $conn->prepare("INSERT INTO inventory (blood_group, collection_date, expiry_date, location, status) VALUES (?, ?, ?, ?, 'available')");
            $stmt->bind_param("ssss", $item[0], $item[1], $item[2], $item[3]);
            $stmt->execute();
        }

        // Sample requests
        $requests = [
            ['Emergency Patient', '911-emergency', 'emergency@hospital.com', 'O-', 'New York', 'Critical', 'New York Emergency', '2024-01-20', 2],
            ['Surgery Patient', '555-1234', 'surgery@medical.com', 'A+', 'Los Angeles', 'High', 'LA Medical Center', '2024-01-25', 1]
        ];

        foreach ($requests as $request) {
            $stmt = $conn->prepare("INSERT INTO requests (requester_name, requester_contact, requester_email, blood_group, location, urgency, hospital, required_date, units_needed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssi", $request[0], $request[1], $request[2], $request[3], $request[4], $request[5], $request[6], $request[7], $request[8]);
            $stmt->execute();
        }

        $conn->close();
    }
}

// Run the setup if called directly
if (php_sapi_name() === 'cli') {
    $setup = new DatabaseSetup();
    $setup->run();
} else {
    echo "This script should be run from the command line.";
}
?>
