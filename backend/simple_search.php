<?php
// simple_search.php - Simplified search that works
header('Content-Type: application/json');

try {
    // Direct database connection without includes
    $conn = new mysqli("localhost", "root", "", "blood_donation");

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get search parameters
    $blood_group = $_GET['blood_group'] ?? '';
    $search_type = $_GET['search_type'] ?? 'donors';

    $results = [
        'donors' => [],
        'total_matches' => 0
    ];

    // Search for donors
    if ($search_type === 'donors' || $search_type === 'both') {
        $sql = "SELECT id, name, blood_group, location, contact, 
                       CASE 
                           WHEN last_donation_date IS NULL THEN 'Available'
                           WHEN DATEDIFF(CURDATE(), last_donation_date) >= 90 THEN 'Available'
                           ELSE 'Not Available'
                       END as availability_status
                FROM donors 
                WHERE verified = 1";

        if (!empty($blood_group)) {
            $sql .= " AND blood_group = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $blood_group);
        } else {
            $stmt = $conn->prepare($sql);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $results['donors'][] = $row;
        }
        $stmt->close();
    }

    $results['total_matches'] = count($results['donors']);

    echo json_encode([
        'success' => true,
        'results' => $results,
        'search_params' => [
            'blood_group' => $blood_group,
            'search_type' => $search_type
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Search error: ' . $e->getMessage()
    ]);
}
?>
