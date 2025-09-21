<?php
// working_search.php - Minimal working search endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');

// Direct database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blood_donation";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get parameters
    $blood_group = $_GET['blood_group'] ?? '';
    $location = $_GET['location'] ?? '';
    $search_type = $_GET['search_type'] ?? 'donors';

    // Search donors
    $sql = "SELECT id, name, blood_group, location, contact FROM donors WHERE verified = 1";
    $params = [];
    $types = "";

    if (!empty($blood_group)) {
        $sql .= " AND blood_group = ?";
        $params[] = $blood_group;
        $types .= "s";
    }

    if (!empty($location)) {
        $sql .= " AND location LIKE ?";
        $params[] = "%$location%";
        $types .= "s";
    }

    $sql .= " ORDER BY name LIMIT 20";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $donors = [];
    while ($row = $result->fetch_assoc()) {
        $row['availability_status'] = 'Available';
        $row['eligible_to_donate'] = 1;
        $row['donation_status'] = 'Available';
        $row['formatted_last_donation'] = 'Never donated';
        $donors[] = $row;
    }

    echo json_encode([
        'success' => true,
        'results' => [
            'donors' => $donors,
            'inventory' => [],
            'total_matches' => count($donors)
        ],
        'search_params' => [
            'blood_group' => $blood_group,
            'location' => $location,
            'search_type' => $search_type
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
