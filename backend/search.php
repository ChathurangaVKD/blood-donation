<?php
// search.php - Enhanced blood and donor search
include 'db.php';

// Set proper headers for CORS and sessions
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" || $_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get search parameters
        $blood_group = sanitizeInput($_REQUEST['blood_group'] ?? '');
        $location = sanitizeInput($_REQUEST['location'] ?? '');
        $search_type = sanitizeInput($_REQUEST['search_type'] ?? 'both'); // 'donors', 'inventory', 'both'

        $results = [
            'donors' => [],
            'inventory' => [],
            'total_matches' => 0
        ];

        // Search in inventory if requested
        if ($search_type === 'inventory' || $search_type === 'both') {
            $inventory_sql = "SELECT i.*, 
                                   DATE_FORMAT(i.expiry_date, '%Y-%m-%d') as formatted_expiry,
                                   DATEDIFF(i.expiry_date, CURDATE()) as days_to_expiry
                            FROM inventory i 
                            WHERE i.status = 'available' AND i.expiry_date > CURDATE()";
            $inventory_params = [];
            $inventory_types = "";

            if (!empty($blood_group)) {
                $inventory_sql .= " AND i.blood_group = ?";
                $inventory_params[] = $blood_group;
                $inventory_types .= "s";
            }

            if (!empty($location)) {
                $inventory_sql .= " AND i.location LIKE ?";
                $inventory_params[] = "%$location%";
                $inventory_types .= "s";
            }

            $inventory_sql .= " ORDER BY i.expiry_date ASC";

            $inventory_stmt = $conn->prepare($inventory_sql);
            if (!empty($inventory_params)) {
                $inventory_stmt->bind_param($inventory_types, ...$inventory_params);
            }
            $inventory_stmt->execute();
            $inventory_result = $inventory_stmt->get_result();

            while ($row = $inventory_result->fetch_assoc()) {
                $row['source_type'] = 'inventory';
                $results['inventory'][] = $row;
            }
            $inventory_stmt->close();
        }

        // Search in verified donors if requested
        if ($search_type === 'donors' || $search_type === 'both') {
            $donor_sql = "SELECT d.id, d.name, d.blood_group, d.location, d.contact,
                               d.last_donation_date,
                               DATE_FORMAT(d.last_donation_date, '%Y-%m-%d') as formatted_last_donation,
                               CASE 
                                   WHEN d.last_donation_date IS NULL THEN 1
                                   WHEN DATEDIFF(CURDATE(), d.last_donation_date) >= 90 THEN 1
                                   ELSE 0
                               END as eligible_to_donate,
                               CASE 
                                   WHEN d.last_donation_date IS NULL THEN 'Never donated'
                                   WHEN DATEDIFF(CURDATE(), d.last_donation_date) >= 90 THEN 'Eligible'
                                   ELSE CONCAT('Wait ', 90 - DATEDIFF(CURDATE(), d.last_donation_date), ' days')
                               END as donation_status
                        FROM donors d 
                        WHERE d.verified = 1";
            $donor_params = [];
            $donor_types = "";

            if (!empty($blood_group)) {
                $donor_sql .= " AND d.blood_group = ?";
                $donor_params[] = $blood_group;
                $donor_types .= "s";
            }

            if (!empty($location)) {
                $donor_sql .= " AND d.location LIKE ?";
                $donor_params[] = "%$location%";
                $donor_types .= "s";
            }

            $donor_sql .= " ORDER BY eligible_to_donate DESC, d.last_donation_date ASC";

            $donor_stmt = $conn->prepare($donor_sql);
            if (!empty($donor_params)) {
                $donor_stmt->bind_param($donor_types, ...$donor_params);
            }
            $donor_stmt->execute();
            $donor_result = $donor_stmt->get_result();

            while ($row = $donor_result->fetch_assoc()) {
                $row['source_type'] = 'donor';
                // Don't expose sensitive information
                unset($row['email']);
                $results['donors'][] = $row;
            }
            $donor_stmt->close();
        }

        $results['total_matches'] = count($results['donors']) + count($results['inventory']);

        // Add blood compatibility information
        if (!empty($blood_group)) {
            $results['compatibility_info'] = getBloodCompatibility($blood_group);
        }

        echo json_encode([
            'success' => true,
            'results' => $results,
            'search_params' => [
                'blood_group' => $blood_group,
                'location' => $location,
                'search_type' => $search_type
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Search error: ' . $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

function getBloodCompatibility($blood_group) {
    $compatibility = [
        'A+' => ['can_donate_to' => ['A+', 'AB+'], 'can_receive_from' => ['A+', 'A-', 'O+', 'O-']],
        'A-' => ['can_donate_to' => ['A+', 'A-', 'AB+', 'AB-'], 'can_receive_from' => ['A-', 'O-']],
        'B+' => ['can_donate_to' => ['B+', 'AB+'], 'can_receive_from' => ['B+', 'B-', 'O+', 'O-']],
        'B-' => ['can_donate_to' => ['B+', 'B-', 'AB+', 'AB-'], 'can_receive_from' => ['B-', 'O-']],
        'AB+' => ['can_donate_to' => ['AB+'], 'can_receive_from' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']],
        'AB-' => ['can_donate_to' => ['AB+', 'AB-'], 'can_receive_from' => ['A-', 'B-', 'AB-', 'O-']],
        'O+' => ['can_donate_to' => ['A+', 'B+', 'AB+', 'O+'], 'can_receive_from' => ['O+', 'O-']],
        'O-' => ['can_donate_to' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], 'can_receive_from' => ['O-']]
    ];

    return $compatibility[$blood_group] ?? null;
}

$conn->close();
?>