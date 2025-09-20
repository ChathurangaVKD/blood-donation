<?php
// inventory.php - Enhanced blood inventory management with comprehensive validation
include 'db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $action = sanitizeInput($_POST['action'] ?? '');

        if ($action === 'add') {
            // Get and sanitize input data
            $blood_group = sanitizeInput($_POST['blood_group'] ?? '');
            $donor_id = !empty($_POST['donor_id']) ? (int)$_POST['donor_id'] : null;
            $collection_date = $_POST['collection_date'] ?? '';
            $expiry_date = $_POST['expiry_date'] ?? '';
            $location = sanitizeInput($_POST['location'] ?? '');
            $notes = sanitizeInput($_POST['notes'] ?? '');

            // Enhanced validation with specific error messages
            $errors = [];

            // Validate required fields
            if (empty($blood_group)) $errors[] = "Blood group is required";
            if (empty($collection_date)) $errors[] = "Collection date is required";
            if (empty($expiry_date)) $errors[] = "Expiry date is required";
            if (empty($location)) $errors[] = "Storage location is required";

            // Validate blood group
            $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            if (!empty($blood_group) && !in_array($blood_group, $valid_blood_groups)) {
                $errors[] = "Please select a valid blood group";
            }

            // Validate dates
            if (!empty($collection_date)) {
                $collection_dt = DateTime::createFromFormat('Y-m-d', $collection_date);
                if (!$collection_dt || $collection_dt->format('Y-m-d') !== $collection_date) {
                    $errors[] = "Please enter a valid collection date";
                } elseif ($collection_dt > new DateTime()) {
                    $errors[] = "Collection date cannot be in the future";
                } elseif ($collection_dt < (new DateTime())->modify('-1 year')) {
                    $errors[] = "Collection date cannot be more than 1 year in the past";
                }
            }

            if (!empty($expiry_date)) {
                $expiry_dt = DateTime::createFromFormat('Y-m-d', $expiry_date);
                if (!$expiry_dt || $expiry_dt->format('Y-m-d') !== $expiry_date) {
                    $errors[] = "Please enter a valid expiry date";
                }
            }

            // Validate date relationship
            if (!empty($collection_date) && !empty($expiry_date)) {
                $collection_dt = DateTime::createFromFormat('Y-m-d', $collection_date);
                $expiry_dt = DateTime::createFromFormat('Y-m-d', $expiry_date);

                if ($collection_dt && $expiry_dt) {
                    if ($expiry_dt <= $collection_dt) {
                        $errors[] = "Expiry date must be after collection date";
                    }

                    $days_diff = $expiry_dt->diff($collection_dt)->days;
                    if ($days_diff > 42) {
                        $errors[] = "Blood cannot be stored for more than 42 days (current: $days_diff days)";
                    } elseif ($days_diff < 1) {
                        $errors[] = "Minimum storage period is 1 day";
                    }
                }
            }

            // Validate location
            if (!empty($location) && (strlen($location) < 2 || !preg_match("/^[a-zA-Z0-9\s.\-,]+$/", $location))) {
                $errors[] = "Location must be at least 2 characters and contain valid characters";
            }

            // Validate donor if provided
            if (!empty($donor_id) && $conn) {
                $donor_check = $conn->prepare("SELECT blood_group, name FROM donors WHERE id = ? AND verified = 1");
                $donor_check->bind_param("i", $donor_id);
                $donor_check->execute();
                $donor_result = $donor_check->get_result();

                if ($donor_result->num_rows === 0) {
                    $errors[] = "Donor not found or not verified";
                } else {
                    $donor_data = $donor_result->fetch_assoc();
                    if ($donor_data['blood_group'] !== $blood_group) {
                        $errors[] = "Blood group mismatch with donor's blood group (" . $donor_data['blood_group'] . ")";
                    }
                }
                $donor_check->close();
            }

            // If validation errors exist, return them
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Inventory addition failed due to validation errors',
                    'errors' => $errors
                ]);
                exit;
            }

            // Insert blood unit to inventory
            if ($conn) {
                $stmt = $conn->prepare("INSERT INTO inventory (blood_group, donor_id, collection_date, expiry_date, location, notes) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sissss", $blood_group, $donor_id, $collection_date, $expiry_date, $location, $notes);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to add blood unit to inventory: " . $stmt->error);
                }

                $inventory_id = $conn->insert_id;
                $stmt->close();

                // Return success response with detailed information
                echo json_encode([
                    'success' => true,
                    'message' => 'Blood unit added to inventory successfully',
                    'inventory_id' => $inventory_id,
                    'data' => [
                        'blood_group' => $blood_group,
                        'collection_date' => $collection_date,
                        'expiry_date' => $expiry_date,
                        'location' => $location,
                        'donor_id' => $donor_id,
                        'days_until_expiry' => (new DateTime($expiry_date))->diff(new DateTime())->days,
                        'status' => 'available'
                    ]
                ]);
            }

        } elseif ($action === 'update') {
            // Get and sanitize input data
            $id = (int)($_POST['id'] ?? 0);
            $status = sanitizeInput($_POST['status'] ?? '');
            $notes = sanitizeInput($_POST['notes'] ?? '');

            // Enhanced validation with specific error messages
            $errors = [];

            // Validate required fields
            if ($id <= 0) $errors[] = "Valid inventory ID is required";
            if (empty($status)) $errors[] = "Status is required";

            // Validate status
            $valid_statuses = ['available', 'reserved', 'used', 'expired'];
            if (!empty($status) && !in_array($status, $valid_statuses)) {
                $errors[] = "Please select a valid status: " . implode(', ', $valid_statuses);
            }

            // Check if inventory item exists
            if (empty($errors) && $conn) {
                $check_stmt = $conn->prepare("SELECT blood_group, status, expiry_date FROM inventory WHERE id = ?");
                $check_stmt->bind_param("i", $id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows === 0) {
                    $errors[] = "Inventory item not found";
                } else {
                    $current_data = $check_result->fetch_assoc();

                    // Validate status transitions
                    if ($current_data['status'] === 'used' && $status !== 'used') {
                        $errors[] = "Cannot change status from 'used' to another status";
                    }

                    if ($current_data['status'] === 'expired' && $status === 'available') {
                        $errors[] = "Cannot make expired blood available again";
                    }
                }
                $check_stmt->close();
            }

            // If validation errors exist, return them
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Inventory update failed due to validation errors',
                    'errors' => $errors
                ]);
                exit;
            }

            // Update inventory item
            if ($conn) {
                $update_sql = "UPDATE inventory SET status = ?, updated_at = CURRENT_TIMESTAMP";
                $params = [$status];
                $types = "s";

                if (!empty($notes)) {
                    $update_sql .= ", notes = ?";
                    $params[] = $notes;
                    $types .= "s";
                }

                $update_sql .= " WHERE id = ?";
                $params[] = $id;
                $types .= "i";

                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param($types, ...$params);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to update inventory: " . $stmt->error);
                }

                if ($stmt->affected_rows === 0) {
                    throw new Exception("No changes were made to the inventory item");
                }

                $stmt->close();

                // Return success response
                echo json_encode([
                    'success' => true,
                    'message' => 'Inventory updated successfully',
                    'data' => [
                        'inventory_id' => $id,
                        'new_status' => $status,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            }

        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Please specify a valid action.',
                'valid_actions' => ['add', 'update']
            ]);
        }

    } catch (Exception $e) {
        // Log error for debugging
        error_log("Inventory error: " . $e->getMessage());

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An internal error occurred while processing inventory request. Please try again or contact support.',
            'error_code' => 'INVENTORY_ERROR'
        ]);
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        // Get query parameters for filtering
        $blood_group = $_GET['blood_group'] ?? '';
        $status = $_GET['status'] ?? 'available';
        $location = $_GET['location'] ?? '';
        $donor_id = $_GET['donor_id'] ?? '';
        $expiring_days = $_GET['expiring_days'] ?? '';

        // Build query with filters
        $sql = "SELECT i.*, d.name as donor_name, d.contact as donor_contact,
                       DATE_FORMAT(i.collection_date, '%Y-%m-%d') as formatted_collection_date,
                       DATE_FORMAT(i.expiry_date, '%Y-%m-%d') as formatted_expiry_date,
                       DATEDIFF(i.expiry_date, CURDATE()) as days_until_expiry,
                       DATE_FORMAT(i.created_at, '%Y-%m-%d %H:%i') as added_time
                FROM inventory i 
                LEFT JOIN donors d ON i.donor_id = d.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($blood_group)) {
            $sql .= " AND i.blood_group = ?";
            $params[] = $blood_group;
            $types .= "s";
        }

        if (!empty($status)) {
            $sql .= " AND i.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if (!empty($location)) {
            $sql .= " AND i.location LIKE ?";
            $params[] = "%$location%";
            $types .= "s";
        }

        if (!empty($donor_id)) {
            $sql .= " AND i.donor_id = ?";
            $params[] = (int)$donor_id;
            $types .= "i";
        }

        if (!empty($expiring_days)) {
            $sql .= " AND DATEDIFF(i.expiry_date, CURDATE()) <= ?";
            $params[] = (int)$expiring_days;
            $types .= "i";
        }

        $sql .= " ORDER BY i.expiry_date ASC, i.created_at DESC";

        if ($conn) {
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $inventory = [];
            $stats = [
                'total_units' => 0,
                'by_blood_group' => [],
                'by_status' => [],
                'expiring_soon' => 0
            ];

            while ($row = $result->fetch_assoc()) {
                $inventory[] = $row;
                $stats['total_units']++;

                // Count by blood group
                if (!isset($stats['by_blood_group'][$row['blood_group']])) {
                    $stats['by_blood_group'][$row['blood_group']] = 0;
                }
                $stats['by_blood_group'][$row['blood_group']]++;

                // Count by status
                if (!isset($stats['by_status'][$row['status']])) {
                    $stats['by_status'][$row['status']] = 0;
                }
                $stats['by_status'][$row['status']]++;

                // Count expiring soon (within 7 days)
                if ($row['days_until_expiry'] <= 7 && $row['days_until_expiry'] >= 0) {
                    $stats['expiring_soon']++;
                }
            }

            // Return enhanced response with statistics
            echo json_encode([
                'success' => true,
                'inventory' => $inventory,
                'statistics' => $stats,
                'filters_applied' => [
                    'blood_group' => $blood_group ?: 'All',
                    'status' => $status,
                    'location' => $location ?: 'All',
                    'expiring_within_days' => $expiring_days ?: 'No limit'
                ]
            ]);

            $stmt->close();
        }

    } catch (Exception $e) {
        // Log error for debugging
        error_log("Inventory retrieval error: " . $e->getMessage());

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while retrieving inventory data. Please try again.',
            'error_code' => 'RETRIEVAL_ERROR'
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use GET to retrieve inventory or POST to manage inventory.'
    ]);
}

$conn->close();
?>