<?php
// donations.php - Enhanced donation records with comprehensive validation
include 'db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $action = sanitizeInput($_POST['action'] ?? '');

        if ($action === 'record_donation') {
            // Get and sanitize input data
            $donor_id = (int)($_POST['donor_id'] ?? 0);
            $donation_date = $_POST['donation_date'] ?? date('Y-m-d');
            $blood_group = sanitizeInput($_POST['blood_group'] ?? '');
            $units_donated = (int)($_POST['units_donated'] ?? 1);
            $location = sanitizeInput($_POST['location'] ?? '');
            $medical_checkup_passed = (int)($_POST['medical_checkup_passed'] ?? 1);
            $notes = sanitizeInput($_POST['notes'] ?? '');

            // Enhanced validation with specific error messages
            $errors = [];

            // Validate required fields
            if ($donor_id <= 0) $errors[] = "Valid donor ID is required";
            if (empty($blood_group)) $errors[] = "Blood group is required";
            if (empty($location)) $errors[] = "Donation location is required";
            if (empty($donation_date)) $errors[] = "Donation date is required";

            // Validate blood group
            $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            if (!empty($blood_group) && !in_array($blood_group, $valid_blood_groups)) {
                $errors[] = "Please select a valid blood group";
            }

            // Validate units donated
            if ($units_donated < 1 || $units_donated > 3) {
                $errors[] = "Units donated must be between 1 and 3";
            }

            // Validate date
            if (!empty($donation_date)) {
                $date = DateTime::createFromFormat('Y-m-d', $donation_date);
                if (!$date || $date->format('Y-m-d') !== $donation_date) {
                    $errors[] = "Please enter a valid donation date";
                } elseif ($date > new DateTime()) {
                    $errors[] = "Donation date cannot be in the future";
                } elseif ($date < (new DateTime())->modify('-1 year')) {
                    $errors[] = "Donation date cannot be more than 1 year in the past";
                }
            }

            // Validate location
            if (!empty($location) && (strlen($location) < 2 || !preg_match("/^[a-zA-Z0-9\s.\-,]+$/", $location))) {
                $errors[] = "Location must be at least 2 characters and contain valid characters";
            }

            // Check if donor exists and is verified
            if (empty($errors) && $conn) {
                $donor_check = $conn->prepare("SELECT blood_group, last_donation_date, name FROM donors WHERE id = ? AND verified = 1");
                $donor_check->bind_param("i", $donor_id);
                $donor_check->execute();
                $donor_result = $donor_check->get_result();

                if ($donor_result->num_rows === 0) {
                    $errors[] = "Donor not found or not verified. Please ensure the donor is registered and verified.";
                } else {
                    $donor_data = $donor_result->fetch_assoc();

                    // Validate blood group matches donor's blood group
                    if ($donor_data['blood_group'] !== $blood_group) {
                        $errors[] = "Blood group mismatch. Donor's blood group is " . $donor_data['blood_group'];
                    }

                    // Check if enough time has passed since last donation (90 days)
                    if ($donor_data['last_donation_date']) {
                        $last_donation = new DateTime($donor_data['last_donation_date']);
                        $current_date = new DateTime($donation_date);
                        $days_diff = $current_date->diff($last_donation)->days;

                        if ($days_diff < 90) {
                            $errors[] = "Donor must wait " . (90 - $days_diff) . " more days before next donation (minimum 90 days between donations)";
                        }
                    }
                }
                $donor_check->close();
            }

            // If validation errors exist, return them
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Donation recording failed due to validation errors',
                    'errors' => $errors
                ]);
                exit;
            }

            // Begin transaction for data consistency
            $conn->begin_transaction();

            try {
                // Record the donation
                $donation_stmt = $conn->prepare("INSERT INTO donations (donor_id, donation_date, blood_group, units_donated, location, medical_checkup_passed, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $donation_stmt->bind_param("issiiss", $donor_id, $donation_date, $blood_group, $units_donated, $location, $medical_checkup_passed, $notes);

                if (!$donation_stmt->execute()) {
                    throw new Exception("Failed to record donation: " . $donation_stmt->error);
                }

                $donation_id = $conn->insert_id;

                // Update donor's last donation date
                $update_donor = $conn->prepare("UPDATE donors SET last_donation_date = ? WHERE id = ?");
                $update_donor->bind_param("si", $donation_date, $donor_id);

                if (!$update_donor->execute()) {
                    throw new Exception("Failed to update donor information: " . $update_donor->error);
                }

                // Add to inventory if medical checkup passed
                if ($medical_checkup_passed) {
                    $expiry_date = date('Y-m-d', strtotime($donation_date . ' + 42 days')); // Blood expires in 42 days
                    $inventory_stmt = $conn->prepare("INSERT INTO inventory (blood_group, donor_id, collection_date, expiry_date, location, status) VALUES (?, ?, ?, ?, ?, 'available')");
                    $inventory_stmt->bind_param("sisss", $blood_group, $donor_id, $donation_date, $expiry_date, $location);

                    if (!$inventory_stmt->execute()) {
                        throw new Exception("Failed to add to inventory: " . $inventory_stmt->error);
                    }
                    $inventory_stmt->close();
                }

                $conn->commit();

                // Return success response with detailed information
                echo json_encode([
                    'success' => true,
                    'message' => 'Donation recorded successfully! Thank you for your contribution.',
                    'donation_id' => $donation_id,
                    'data' => [
                        'donor_name' => $donor_data['name'],
                        'blood_group' => $blood_group,
                        'units_donated' => $units_donated,
                        'donation_date' => $donation_date,
                        'location' => $location,
                        'added_to_inventory' => $medical_checkup_passed ? true : false,
                        'next_eligible_date' => date('Y-m-d', strtotime($donation_date . ' + 90 days'))
                    ]
                ]);

                $donation_stmt->close();
                $update_donor->close();

            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }

        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Please specify a valid action.',
                'valid_actions' => ['record_donation']
            ]);
        }

    } catch (Exception $e) {
        // Log error for debugging
        error_log("Donation recording error: " . $e->getMessage());

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An internal error occurred while recording the donation. Please try again or contact support.',
            'error_code' => 'DONATION_ERROR'
        ]);
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        // Get query parameters for filtering
        $donor_id = $_GET['donor_id'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $blood_group = $_GET['blood_group'] ?? '';
        $location = $_GET['location'] ?? '';

        // Build query with filters
        $sql = "SELECT d.*, dn.name as donor_name, dn.email as donor_email, dn.contact as donor_contact,
                       DATE_FORMAT(d.donation_date, '%Y-%m-%d') as formatted_date,
                       DATE_FORMAT(d.created_at, '%Y-%m-%d %H:%i') as recorded_time
                FROM donations d 
                JOIN donors dn ON d.donor_id = dn.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($donor_id)) {
            $sql .= " AND d.donor_id = ?";
            $params[] = (int)$donor_id;
            $types .= "i";
        }

        if (!empty($blood_group)) {
            $sql .= " AND d.blood_group = ?";
            $params[] = $blood_group;
            $types .= "s";
        }

        if (!empty($location)) {
            $sql .= " AND d.location LIKE ?";
            $params[] = "%$location%";
            $types .= "s";
        }

        if (!empty($start_date)) {
            $sql .= " AND d.donation_date >= ?";
            $params[] = $start_date;
            $types .= "s";
        }

        if (!empty($end_date)) {
            $sql .= " AND d.donation_date <= ?";
            $params[] = $end_date;
            $types .= "s";
        }

        $sql .= " ORDER BY d.donation_date DESC, d.created_at DESC";

        if ($conn) {
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $donations = [];
            $total_units = 0;
            while ($row = $result->fetch_assoc()) {
                $donations[] = $row;
                $total_units += $row['units_donated'];
            }

            // Return enhanced response with statistics
            echo json_encode([
                'success' => true,
                'donations' => $donations,
                'statistics' => [
                    'total_donations' => count($donations),
                    'total_units_collected' => $total_units,
                    'date_range' => [
                        'start' => $start_date ?: 'All time',
                        'end' => $end_date ?: 'Present'
                    ]
                ]
            ]);

            $stmt->close();
        }

    } catch (Exception $e) {
        // Log error for debugging
        error_log("Donation retrieval error: " . $e->getMessage());

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while retrieving donation records. Please try again.',
            'error_code' => 'RETRIEVAL_ERROR'
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use GET to retrieve donations or POST to record donations.'
    ]);
}

$conn->close();
?>
