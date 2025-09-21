<?php
// db_check.php - Check database status and donor count
include 'db.php';

header('Content-Type: application/json');

$response = [
    'database_connection' => false,
    'database_exists' => false,
    'tables_exist' => false,
    'donor_count' => 0,
    'donors' => [],
    'errors' => []
];

try {
    // Check if connection is successful
    if (!$conn->connect_error) {
        $response['database_connection'] = true;
        $response['database_exists'] = true;

        // Check if donors table exists
        $result = $conn->query("SHOW TABLES LIKE 'donors'");
        if ($result && $result->num_rows > 0) {
            $response['tables_exist'] = true;

            // Count donors
            $count_result = $conn->query("SELECT COUNT(*) as count FROM donors");
            if ($count_result) {
                $count_row = $count_result->fetch_assoc();
                $response['donor_count'] = (int)$count_row['count'];
            }

            // Get all donors
            $donors_result = $conn->query("SELECT id, name, email, blood_group, location, contact FROM donors LIMIT 10");
            if ($donors_result) {
                while ($row = $donors_result->fetch_assoc()) {
                    $response['donors'][] = $row;
                }
            }
        } else {
            $response['errors'][] = "Donors table does not exist";
        }
    } else {
        $response['errors'][] = "Database connection failed: " . $conn->connect_error;
    }
} catch (Exception $e) {
    $response['errors'][] = "Exception: " . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
