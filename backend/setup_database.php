<?php
// setup_database.php - Initialize database with expected donors
include 'db.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'steps' => [],
    'donor_count' => 0,
    'errors' => []
];

try {
    // Step 1: Create database if it doesn't exist
    $temp_conn = new mysqli("localhost", "root", "");
    if (!$temp_conn->connect_error) {
        $temp_conn->query("CREATE DATABASE IF NOT EXISTS blood_donation");
        $temp_conn->close();
        $response['steps'][] = "Database 'blood_donation' created/verified";
    }

    // Step 2: Create connection to the database
    $conn = new mysqli("localhost", "root", "", "blood_donation");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $response['steps'][] = "Connected to blood_donation database";

    // Step 3: Create donors table
    $create_table_sql = "
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
        verified BOOLEAN DEFAULT TRUE,
        last_donation_date DATE NULL,
        medical_history TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_blood_group (blood_group),
        INDEX idx_location (location)
    )";

    if ($conn->query($create_table_sql)) {
        $response['steps'][] = "Donors table created/verified";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }

    // Step 4: Check current donor count
    $count_result = $conn->query("SELECT COUNT(*) as count FROM donors");
    $current_count = 0;
    if ($count_result) {
        $count_row = $count_result->fetch_assoc();
        $current_count = (int)$count_row['count'];
    }

    $response['steps'][] = "Current donors in database: $current_count";

    // Step 5: Insert the expected 3 donors if they don't exist
    if ($current_count < 3) {
        // Clear existing data first
        $conn->query("DELETE FROM donors");

        $donors = [
            [
                'name' => 'Dasun Chathuranga',
                'age' => 28,
                'gender' => 'Male',
                'blood_group' => 'A-',
                'contact' => '+94719436366',
                'location' => 'Colombo District, Western Province, Sri Lanka',
                'email' => 'vkdchathuranga@gmail.com',
                'password' => password_hash('dasun123', PASSWORD_DEFAULT),
                'verified' => 1,
                'last_donation_date' => '2025-06-15'
            ],
            [
                'name' => 'Sarah Johnson',
                'age' => 32,
                'gender' => 'Female',
                'blood_group' => 'O+',
                'contact' => '+1555123456',
                'location' => 'New York, NY, USA',
                'email' => 'sarah.johnson@email.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'verified' => 1,
                'last_donation_date' => '2025-08-20'
            ],
            [
                'name' => 'Michael Chen',
                'age' => 26,
                'gender' => 'Male',
                'blood_group' => 'B+',
                'contact' => '+1555987654',
                'location' => 'Los Angeles, CA, USA',
                'email' => 'michael.chen@email.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'verified' => 1,
                'last_donation_date' => '2025-07-10'
            ]
        ];

        $insert_sql = "INSERT INTO donors (name, age, gender, blood_group, contact, location, email, password, verified, last_donation_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);

        foreach ($donors as $donor) {
            $stmt->bind_param("sissssssss",
                $donor['name'],
                $donor['age'],
                $donor['gender'],
                $donor['blood_group'],
                $donor['contact'],
                $donor['location'],
                $donor['email'],
                $donor['password'],
                $donor['verified'],
                $donor['last_donation_date']
            );

            if ($stmt->execute()) {
                $response['steps'][] = "Inserted donor: " . $donor['name'] . " (" . $donor['blood_group'] . ")";
            } else {
                $response['errors'][] = "Failed to insert donor: " . $donor['name'] . " - " . $stmt->error;
            }
        }
        $stmt->close();
    }

    // Step 6: Verify final count
    $final_count_result = $conn->query("SELECT COUNT(*) as count FROM donors");
    if ($final_count_result) {
        $final_count_row = $final_count_result->fetch_assoc();
        $response['donor_count'] = (int)$final_count_row['count'];
    }

    // Step 7: Get donor list
    $donors_result = $conn->query("SELECT id, name, email, blood_group, location FROM donors ORDER BY id");
    $response['donors'] = [];
    if ($donors_result) {
        while ($row = $donors_result->fetch_assoc()) {
            $response['donors'][] = $row;
        }
    }

    $response['success'] = true;
    $response['message'] = "Database setup completed successfully with {$response['donor_count']} donors";

} catch (Exception $e) {
    $response['errors'][] = $e->getMessage();
    $response['message'] = "Database setup failed: " . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
