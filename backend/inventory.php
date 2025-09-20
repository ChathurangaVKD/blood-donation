<?php
// inventory.php - Enhanced blood inventory management
include 'db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $action = sanitizeInput($_POST['action'] ?? '');

        if ($action === 'add') {
            // Add new blood unit to inventory
            $blood_group = sanitizeInput($_POST['blood_group'] ?? '');
            $donor_id = !empty($_POST['donor_id']) ? (int)$_POST['donor_id'] : null;
            $collection_date = $_POST['collection_date'] ?? '';
            $expiry_date = $_POST['expiry_date'] ?? '';
            $location = sanitizeInput($_POST['location'] ?? '');
            $notes = sanitizeInput($_POST['notes'] ?? '');

            // Validate required fields
            if (empty($blood_group) || empty($collection_date) || empty($expiry_date) || empty($location)) {
                throw new Exception("Blood group, collection date, expiry date, and location are required");
            }

            // Validate blood group
            $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            if (!in_array($blood_group, $valid_blood_groups)) {
                throw new Exception("Invalid blood group");
            }

            // Validate dates
            $collection_dt = DateTime::createFromFormat('Y-m-d', $collection_date);
            $expiry_dt = DateTime::createFromFormat('Y-m-d', $expiry_date);

            if (!$collection_dt || !$expiry_dt) {
                throw new Exception("Invalid date format");
            }

            if ($expiry_dt <= $collection_dt) {
                throw new Exception("Expiry date must be after collection date");
            }

            if ($collection_dt > new DateTime()) {
                throw new Exception("Collection date cannot be in the future");
            }

            $stmt = $conn->prepare("INSERT INTO inventory (blood_group, donor_id, collection_date, expiry_date, location, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissss", $blood_group, $donor_id, $collection_date, $expiry_date, $location, $notes);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Blood unit added to inventory successfully.',
                    'inventory_id' => $conn->insert_id
                ]);
            } else {
                throw new Exception("Error adding blood unit: " . $stmt->error);
            }
            $stmt->close();

        } elseif ($action === 'update') {
            // Update inventory item status
            $id = (int)($_POST['id'] ?? 0);
            $status = sanitizeInput($_POST['status'] ?? '');

            if ($id <= 0) {
                throw new Exception("Invalid inventory ID");
            }

            $valid_statuses = ['available', 'reserved', 'used', 'expired'];
            if (!in_array($status, $valid_statuses)) {
                throw new Exception("Invalid status");
            }

            $stmt = $conn->prepare("UPDATE inventory SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("si", $status, $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Inventory updated successfully.'
                    ]);
                } else {
                    throw new Exception("Inventory item not found");
                }
            } else {
                throw new Exception("Error updating inventory: " . $stmt->error);
            }
            $stmt->close();

        } else {
            throw new Exception("Invalid action");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        // Get inventory with filters
        $status = $_GET['status'] ?? '';
        $blood_group = $_GET['blood_group'] ?? '';
        $location = $_GET['location'] ?? '';
        $expired_only = $_GET['expired'] ?? false;

        $sql = "SELECT i.*, 
                       d.name as donor_name,
                       DATE_FORMAT(i.collection_date, '%Y-%m-%d') as formatted_collection_date,
                       DATE_FORMAT(i.expiry_date, '%Y-%m-%d') as formatted_expiry_date,
                       DATEDIFF(i.expiry_date, CURDATE()) as days_to_expiry,
                       CASE 
                           WHEN i.expiry_date <= CURDATE() THEN 'expired'
                           WHEN DATEDIFF(i.expiry_date, CURDATE()) <= 7 THEN 'expiring_soon'
                           ELSE 'fresh'
                       END as freshness_status
                FROM inventory i 
                LEFT JOIN donors d ON i.donor_id = d.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($status)) {
            $sql .= " AND i.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if (!empty($blood_group)) {
            $sql .= " AND i.blood_group = ?";
            $params[] = $blood_group;
            $types .= "s";
        }

        if (!empty($location)) {
            $sql .= " AND i.location LIKE ?";
            $params[] = "%$location%";
            $types .= "s";
        }

        if ($expired_only) {
            $sql .= " AND i.expiry_date <= CURDATE()";
        }

        $sql .= " ORDER BY i.expiry_date ASC, i.blood_group";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $inventory = [];
        $stats = [
            'total_units' => 0,
            'available_units' => 0,
            'expired_units' => 0,
            'expiring_soon' => 0,
            'by_blood_group' => []
        ];

        while ($row = $result->fetch_assoc()) {
            $inventory[] = $row;
            $stats['total_units']++;

            if ($row['status'] === 'available') {
                $stats['available_units']++;
            }

            if ($row['freshness_status'] === 'expired') {
                $stats['expired_units']++;
            } elseif ($row['freshness_status'] === 'expiring_soon') {
                $stats['expiring_soon']++;
            }

            // Count by blood group
            $bg = $row['blood_group'];
            if (!isset($stats['by_blood_group'][$bg])) {
                $stats['by_blood_group'][$bg] = 0;
            }
            if ($row['status'] === 'available' && $row['freshness_status'] !== 'expired') {
                $stats['by_blood_group'][$bg]++;
            }
        }

        echo json_encode([
            'success' => true,
            'inventory' => $inventory,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'blood_group' => $blood_group,
                'location' => $location,
                'expired_only' => $expired_only
            ]
        ]);

        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching inventory: ' . $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

$conn->close();
?>