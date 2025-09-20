#!/usr/bin/env php
<?php
// reset_and_populate.php - Reset database and populate with comprehensive sample data
require_once __DIR__ . '/config.php';

class DatabaseReset {
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
        echo "🩸 Blood Donation System - Database Reset & Enhanced Population\n";
        echo "================================================================\n\n";

        try {
            // Step 1: Connect to database
            echo "1. 🔗 Connecting to MySQL...\n";
            $conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            echo "   ✅ Connected to database '{$this->dbname}'\n\n";

            // Step 2: Clear existing data safely
            echo "2. 🧹 Clearing existing data...\n";
            $this->clearExistingData($conn);
            echo "   ✅ Existing data cleared\n\n";

            // Step 3: Populate with comprehensive sample data
            echo "3. 📊 Populating with comprehensive sample data...\n";
            $this->populateComprehensiveData($conn);
            echo "   ✅ Sample data populated successfully\n\n";

            // Step 4: Verify data
            echo "4. 🔍 Verifying data integrity...\n";
            $this->verifyData($conn);
            echo "   ✅ Data verification complete\n\n";

            echo "🎉 Database reset and population completed successfully!\n\n";
            $this->showStatistics($conn);

            $conn->close();

        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function clearExistingData($conn) {
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Clear tables in correct order to handle foreign key constraints
        $tables = ['request_fulfillments', 'donations', 'requests', 'inventory', 'donors'];
        foreach ($tables as $table) {
            $conn->query("DELETE FROM $table");
            echo "   🗑️  Cleared table: $table\n";
        }

        // Clear admins except the default one
        $conn->query("DELETE FROM admins WHERE id > 1");
        echo "   🗑️  Cleared additional admin users\n";

        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    private function populateComprehensiveData($conn) {
        // Insert comprehensive donors (28 donors)
        echo "   👥 Inserting comprehensive donor records...\n";
        $donors = [
            // O+ donors (Universal donor for Rh+ recipients)
            ['John Doe', 28, 'Male', 'O+', '+1-555-0101', 'New York', 'john.doe@email.com', 1, '2025-06-15', 'No known allergies'],
            ['Michael Brown', 35, 'Male', 'O+', '+1-555-0201', 'Los Angeles', 'michael.brown@email.com', 1, '2025-07-01', 'Regular donor, excellent health'],
            ['Emily Davis', 29, 'Female', 'O+', '+1-555-0301', 'Chicago', 'emily.davis@email.com', 1, '2025-08-10', 'No medical issues'],
            ['Robert Wilson', 32, 'Male', 'O+', '+1-555-0401', 'Houston', 'robert.wilson@email.com', 1, NULL, 'First time donor'],

            // O- donors (Universal donor)
            ['David Brown', 29, 'Male', 'O-', '+1-555-0105', 'Phoenix', 'david.brown@email.com', 1, '2025-08-01', 'Universal donor'],
            ['Jessica Garcia', 26, 'Female', 'O-', '+1-555-0205', 'Philadelphia', 'jessica.garcia@email.com', 1, '2025-07-15', 'Excellent health'],
            ['Christopher Lee', 31, 'Male', 'O-', '+1-555-0305', 'San Antonio', 'christopher.lee@email.com', 1, '2025-06-20', 'Regular donor'],

            // A+ donors
            ['Jane Smith', 32, 'Female', 'A+', '+1-555-0102', 'Los Angeles', 'jane.smith@email.com', 1, '2025-07-20', 'No known issues'],
            ['Daniel Rodriguez', 27, 'Male', 'A+', '+1-555-0202', 'San Diego', 'daniel.rodriguez@email.com', 1, '2025-08-05', 'Healthy donor'],
            ['Amanda Johnson', 30, 'Female', 'A+', '+1-555-0302', 'Dallas', 'amanda.johnson@email.com', 1, '2025-06-28', 'Regular donor'],
            ['Kevin Martinez', 33, 'Male', 'A+', '+1-555-0402', 'San Jose', 'kevin.martinez@email.com', 1, NULL, 'New donor'],

            // A- donors
            ['Lisa Davis', 26, 'Female', 'A-', '+1-555-0106', 'Philadelphia', 'lisa.davis@email.com', 1, '2025-08-15', 'No allergies'],
            ['Thomas Anderson', 34, 'Male', 'A-', '+1-555-0206', 'Austin', 'thomas.anderson@email.com', 1, '2025-07-10', 'Experienced donor'],
            ['Rachel White', 28, 'Female', 'A-', '+1-555-0306', 'Jacksonville', 'rachel.white@email.com', 1, '2025-06-25', 'Good health'],

            // B+ donors
            ['Mark Thompson', 31, 'Male', 'B+', '+1-555-0107', 'Fort Worth', 'mark.thompson@email.com', 1, '2025-08-20', 'No medical history'],
            ['Nicole Taylor', 29, 'Female', 'B+', '+1-555-0207', 'Columbus', 'nicole.taylor@email.com', 1, '2025-07-05', 'Regular donor'],
            ['Steven Clark', 36, 'Male', 'B+', '+1-555-0307', 'Charlotte', 'steven.clark@email.com', 1, NULL, 'First donation'],

            // B- donors
            ['Mike Johnson', 25, 'Male', 'B-', '+1-555-0103', 'Chicago', 'mike.johnson@email.com', 1, '2025-08-01', 'Rare blood type'],
            ['Ashley Lewis', 27, 'Female', 'B-', '+1-555-0203', 'Indianapolis', 'ashley.lewis@email.com', 1, '2025-07-12', 'Healthy donor'],
            ['Ryan Walker', 30, 'Male', 'B-', '+1-555-0303', 'Seattle', 'ryan.walker@email.com', 1, '2025-06-30', 'Good condition'],

            // AB+ donors (Universal plasma donor)
            ['Sarah Wilson', 35, 'Female', 'AB+', '+1-555-0104', 'Houston', 'sarah.wilson@email.com', 1, '2025-05-10', 'Universal plasma donor'],
            ['James Hall', 32, 'Male', 'AB+', '+1-555-0204', 'Denver', 'james.hall@email.com', 1, '2025-07-25', 'Regular plasma donor'],
            ['Michelle Young', 28, 'Female', 'AB+', '+1-555-0304', 'Washington', 'michelle.young@email.com', 1, '2025-08-08', 'Excellent health'],
            ['Jason King', 29, 'Male', 'AB+', '+1-555-0404', 'Boston', 'jason.king@email.com', 1, NULL, 'New donor'],

            // AB- donors (Rarest blood type)
            ['Jennifer Green', 33, 'Female', 'AB-', '+1-555-0108', 'El Paso', 'jennifer.green@email.com', 1, '2025-07-18', 'Rare blood type'],
            ['Matthew Adams', 31, 'Male', 'AB-', '+1-555-0208', 'Detroit', 'matthew.adams@email.com', 1, '2025-06-05', 'Very rare donor'],
            ['Stephanie Baker', 26, 'Female', 'AB-', '+1-555-0308', 'Memphis', 'stephanie.baker@email.com', 1, '2025-08-12', 'Healthy rare donor'],

            // Additional variety
            ['Brandon Scott', 34, 'Male', 'O+', '+1-555-0501', 'Portland', 'brandon.scott@email.com', 1, '2025-07-30', 'Regular donor'],
            ['Samantha Hill', 25, 'Female', 'A+', '+1-555-0502', 'Oklahoma City', 'samantha.hill@email.com', 1, '2025-08-18', 'First time donor']
        ];

        $hashed_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password123

        foreach ($donors as $donor) {
            $stmt = $conn->prepare("INSERT INTO donors (name, age, gender, blood_group, contact, location, email, password, verified, last_donation_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissssssis", $donor[0], $donor[1], $donor[2], $donor[3], $donor[4], $donor[5], $donor[6], $hashed_password, $donor[7], $donor[8]);
            $stmt->execute();
            $stmt->close();
        }

        echo "   ✅ Inserted " . count($donors) . " donor records\n";

        // Insert comprehensive inventory (45 units)
        echo "   🩸 Inserting comprehensive inventory records...\n";
        $inventory = [
            // O+ inventory (High demand)
            ['O+', 1, '2025-09-01', '2025-10-13', 'New York Blood Center', 'available', 'Fresh donation - excellent quality'],
            ['O+', 3, '2025-09-03', '2025-10-15', 'Chicago Medical Center', 'available', 'Good condition'],
            ['O+', 4, '2025-09-05', '2025-10-17', 'Houston Blood Bank', 'available', 'Regular donor contribution'],
            ['O+', 26, '2025-09-07', '2025-10-19', 'Portland Medical Center', 'available', 'Fresh collection'],
            ['O+', 2, '2025-09-10', '2025-10-22', 'LA Medical Center', 'reserved', 'Reserved for emergency surgery'],
            ['O+', 1, '2025-09-12', '2025-10-24', 'New York Blood Center', 'available', 'Second donation this month'],

            // O- inventory (Universal donor - highest priority)
            ['O-', 5, '2025-09-02', '2025-10-14', 'Phoenix Blood Bank', 'available', 'Universal donor - critical supply'],
            ['O-', 6, '2025-09-04', '2025-10-16', 'Philadelphia Medical Center', 'available', 'Emergency reserve'],
            ['O-', 7, '2025-09-06', '2025-10-18', 'San Antonio Hospital', 'available', 'Fresh universal donor blood'],
            ['O-', 5, '2025-09-11', '2025-10-23', 'Phoenix Blood Bank', 'reserved', 'Reserved for trauma center'],
            ['O-', 6, '2025-09-13', '2025-10-25', 'Philadelphia Medical Center', 'available', 'High priority stock'],

            // A+ inventory (Common type)
            ['A+', 8, '2025-09-01', '2025-10-13', 'LA Medical Center', 'available', 'Good quality donation'],
            ['A+', 9, '2025-09-03', '2025-10-15', 'San Diego Blood Center', 'available', 'Fresh collection'],
            ['A+', 10, '2025-09-05', '2025-10-17', 'Dallas Medical Center', 'available', 'Regular donor'],
            ['A+', 11, '2025-09-07', '2025-10-19', 'San Jose Hospital', 'available', 'New donor contribution'],
            ['A+', 27, '2025-09-09', '2025-10-21', 'Oklahoma City Blood Bank', 'available', 'First donation'],
            ['A+', 8, '2025-09-14', '2025-10-26', 'LA Medical Center', 'available', 'Second donation'],

            // A- inventory
            ['A-', 12, '2025-09-02', '2025-10-14', 'Philadelphia Medical Center', 'available', 'Rare type - good condition'],
            ['A-', 13, '2025-09-04', '2025-10-16', 'Austin Blood Center', 'available', 'Experienced donor'],
            ['A-', 14, '2025-09-06', '2025-10-18', 'Jacksonville Hospital', 'available', 'Quality donation'],
            ['A-', 12, '2025-09-11', '2025-10-23', 'Philadelphia Medical Center', 'available', 'Regular donor return'],

            // B+ inventory
            ['B+', 15, '2025-09-01', '2025-10-13', 'Fort Worth Medical Center', 'available', 'Fresh donation'],
            ['B+', 16, '2025-09-03', '2025-10-15', 'Columbus Blood Bank', 'available', 'Regular donor'],
            ['B+', 17, '2025-09-05', '2025-10-17', 'Charlotte Hospital', 'available', 'First time donor'],
            ['B+', 15, '2025-09-12', '2025-10-24', 'Fort Worth Medical Center', 'available', 'Second collection'],

            // B- inventory (Rare type)
            ['B-', 18, '2025-09-02', '2025-10-14', 'Chicago General Hospital', 'available', 'Rare type - excellent condition'],
            ['B-', 19, '2025-09-04', '2025-10-16', 'Indianapolis Medical Center', 'available', 'Healthy donor'],
            ['B-', 20, '2025-09-06', '2025-10-18', 'Seattle Blood Bank', 'available', 'Good quality rare blood'],
            ['B-', 18, '2025-09-10', '2025-10-22', 'Chicago General Hospital', 'available', 'Regular rare donor'],

            // AB+ inventory (Universal plasma donor)
            ['AB+', 21, '2025-09-01', '2025-10-13', 'Houston Medical Center', 'available', 'Universal plasma - high value'],
            ['AB+', 22, '2025-09-03', '2025-10-15', 'Denver Blood Center', 'available', 'Regular plasma donor'],
            ['AB+', 23, '2025-09-05', '2025-10-17', 'Washington Hospital', 'available', 'Excellent health donor'],
            ['AB+', 24, '2025-09-07', '2025-10-19', 'Boston Medical Center', 'available', 'New plasma donor'],
            ['AB+', 21, '2025-09-11', '2025-10-23', 'Houston Medical Center', 'available', 'Second plasma donation'],

            // AB- inventory (Rarest blood type)
            ['AB-', 25, '2025-09-02', '2025-10-14', 'El Paso Blood Bank', 'available', 'Extremely rare - handle with care'],
            ['AB-', 26, '2025-09-04', '2025-10-16', 'Detroit Medical Center', 'available', 'Rare donor - premium quality'],
            ['AB-', 27, '2025-09-06', '2025-10-18', 'Memphis Hospital', 'available', 'Rarest type - excellent condition'],
            ['AB-', 25, '2025-09-12', '2025-10-24', 'El Paso Blood Bank', 'reserved', 'Reserved for rare blood patient'],

            // Additional mixed inventory
            ['O+', 2, '2025-08-28', '2025-10-09', 'LA Medical Center', 'available', 'Slightly older but good'],
            ['A+', 10, '2025-08-30', '2025-10-11', 'Dallas Medical Center', 'available', 'Near expiration - use soon'],
            ['B+', 17, '2025-09-18', '2025-10-30', 'Charlotte Hospital', 'available', 'Very fresh donation'],
            ['O-', 7, '2025-09-19', '2025-10-31', 'San Antonio Hospital', 'available', 'Latest universal donor collection'],
            ['AB+', 23, '2025-09-20', '2025-11-01', 'Washington Hospital', 'available', 'Today\'s collection - premium'],

            // More variety for thorough testing
            ['O+', 4, '2025-09-15', '2025-10-27', 'Houston Blood Bank', 'available', 'Recent collection'],
            ['A-', 13, '2025-09-17', '2025-10-29', 'Austin Blood Center', 'available', 'High quality'],
            ['B-', 19, '2025-09-16', '2025-10-28', 'Indianapolis Medical Center', 'available', 'Rare donation'],
            ['O-', 6, '2025-09-18', '2025-10-30', 'Philadelphia Medical Center', 'available', 'Universal critical supply']
        ];

        foreach ($inventory as $item) {
            $stmt = $conn->prepare("INSERT INTO inventory (blood_group, donor_id, collection_date, expiry_date, location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssss", $item[0], $item[1], $item[2], $item[3], $item[4], $item[5], $item[6]);
            $stmt->execute();
            $stmt->close();
        }

        echo "   ✅ Inserted " . count($inventory) . " inventory records\n";

        // Insert sample requests
        echo "   📋 Inserting sample blood requests...\n";
        $requests = [
            ['Dr. Emergency Room', '+1-911-0001', 'emergency@hospital.com', 'O-', 'New York', 'Critical', 'NYC Emergency Hospital', '2025-09-21', 3, 'Car accident victim, immediate need', 'pending'],
            ['Dr. Surgery Dept', '+1-555-1001', 'surgery@medical.com', 'A+', 'Los Angeles', 'High', 'LA Medical Center', '2025-09-25', 2, 'Scheduled surgery patient', 'pending'],
            ['Dr. Oncology', '+1-555-1002', 'oncology@cancer.org', 'B-', 'Chicago', 'Medium', 'Chicago Cancer Center', '2025-09-30', 1, 'Cancer patient treatment', 'pending'],
            ['Dr. Maternity', '+1-555-1003', 'maternity@womens.com', 'AB+', 'Houston', 'High', 'Womens Hospital Houston', '2025-09-23', 2, 'Complications during delivery', 'pending'],
            ['Dr. Pediatrics', '+1-555-1004', 'pediatrics@children.org', 'O+', 'Phoenix', 'Low', 'Phoenix Children Hospital', '2025-10-05', 1, 'Routine procedure', 'pending']
        ];

        foreach ($requests as $request) {
            $stmt = $conn->prepare("INSERT INTO requests (requester_name, requester_contact, requester_email, blood_group, location, urgency, hospital, required_date, units_needed, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssss", $request[0], $request[1], $request[2], $request[3], $request[4], $request[5], $request[6], $request[7], $request[8], $request[9], $request[10]);
            $stmt->execute();
            $stmt->close();
        }

        echo "   ✅ Inserted " . count($requests) . " request records\n";
    }

    private function verifyData($conn) {
        $result = $conn->query("SELECT COUNT(*) as count FROM donors");
        $donorCount = $result->fetch_assoc()['count'];
        echo "   📊 Donors: {$donorCount} records\n";

        $result = $conn->query("SELECT COUNT(*) as count FROM inventory WHERE status = 'available'");
        $inventoryCount = $result->fetch_assoc()['count'];
        echo "   🩸 Available Blood Units: {$inventoryCount} records\n";

        $result = $conn->query("SELECT COUNT(*) as count FROM requests");
        $requestCount = $result->fetch_assoc()['count'];
        echo "   📋 Blood Requests: {$requestCount} records\n";
    }

    private function showStatistics($conn) {
        echo "📈 Database Statistics:\n";
        echo "======================\n";

        // Blood type distribution for donors
        echo "🩸 Donor Blood Type Distribution:\n";
        $result = $conn->query("SELECT blood_group, COUNT(*) as count FROM donors GROUP BY blood_group ORDER BY blood_group");
        while ($row = $result->fetch_assoc()) {
            echo "   {$row['blood_group']}: {$row['count']} donors\n";
        }

        echo "\n📦 Available Inventory by Blood Type:\n";
        $result = $conn->query("SELECT blood_group, COUNT(*) as count FROM inventory WHERE status = 'available' GROUP BY blood_group ORDER BY blood_group");
        while ($row = $result->fetch_assoc()) {
            echo "   {$row['blood_group']}: {$row['count']} units\n";
        }

        echo "\n🌍 Top Locations:\n";
        $result = $conn->query("SELECT location, COUNT(*) as count FROM donors GROUP BY location ORDER BY count DESC LIMIT 8");
        while ($row = $result->fetch_assoc()) {
            echo "   {$row['location']}: {$row['count']} donors\n";
        }

        echo "\n🔐 System Access:\n";
        echo "   Admin Username: admin\n";
        echo "   Admin Password: admin123\n";
        echo "   Frontend: search.html\n";
        echo "   Backend: http://localhost:8081\n";
    }
}

// Run the reset if called directly
if (php_sapi_name() === 'cli') {
    $reset = new DatabaseReset();
    $reset->run();
} else {
    echo "This script should be run from the command line.";
}
?>
