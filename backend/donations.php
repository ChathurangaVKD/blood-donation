<?php
// donations.php - Track donation records
include 'db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $action = sanitizeInput($_POST['action'] ?? '');

        if ($action === 'record_donation') {
            $donor_id = (int)($_POST['donor_id'] ?? 0);
            $donation_date = $_POST['donation_date'] ?? date('Y-m-d');
            $blood_group = sanitizeInput($_POST['blood_group'] ?? '');
            $units_donated = (int)($_POST['units_donated'] ?? 1);
            $location = sanitizeInput($_POST['location'] ?? '');
            $medical_checkup_passed = (int)($_POST['medical_checkup_passed'] ?? 1);
            $notes = sanitizeInput($_POST['notes'] ?? '');

            // Validate required fields
            if ($donor_id <= 0 || empty($blood_group) || empty($location)) {
                throw new Exception("Donor ID, blood group, and location are required");
            }

            // Validate blood group
            $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            if (!in_array($blood_group, $valid_blood_groups)) {
                throw new Exception("Invalid blood group");
            }

            // Check if donor exists and is verified
            $donor_check = $conn->prepare("SELECT blood_group, last_donation_date FROM donors WHERE id = ? AND verified = 1");
            $donor_check->bind_param("i", $donor_id);
            $donor_check->execute();
            $donor_result = $donor_check->get_result();

            if ($donor_result->num_rows === 0) {
                throw new Exception("Donor not found or not verified");
            }

            $donor_data = $donor_result->fetch_assoc();

            // Check if enough time has passed since last donation (90 days)
            if ($donor_data['last_donation_date']) {
                $last_donation = new DateTime($donor_data['last_donation_date']);
                $current_date = new DateTime($donation_date);
                $days_diff = $current_date->diff($last_donation)->days;

                if ($days_diff < 90) {
                    throw new Exception("Donor must wait " . (90 - $days_diff) . " more days before next donation");
                }
            }

            // Begin transaction
            $conn->begin_transaction();

            try {
                // Record the donation
                $donation_stmt = $conn->prepare("INSERT INTO donations (donor_id, donation_date, blood_group, units_donated, location, medical_checkup_passed, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $donation_stmt->bind_param("issiiss", $donor_id, $donation_date, $blood_group, $units_donated, $location, $medical_checkup_passed, $notes);
                $donation_stmt->execute();

                // Update donor's last donation date
                $update_donor = $conn->prepare("UPDATE donors SET last_donation_date = ? WHERE id = ?");
                $update_donor->bind_param("si", $donation_date, $donor_id);
                $update_donor->execute();

                // Add to inventory if medical checkup passed
                if ($medical_checkup_passed) {
                    $expiry_date = date('Y-m-d', strtotime($donation_date . ' + 42 days')); // Blood expires in 42 days
                    $inventory_stmt = $conn->prepare("INSERT INTO inventory (blood_group, donor_id, collection_date, expiry_date, location, status) VALUES (?, ?, ?, ?, ?, 'available')");
                    $inventory_stmt->bind_param("sisss", $blood_group, $donor_id, $donation_date, $expiry_date, $location);
                    $inventory_stmt->execute();
                }

                $conn->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Donation recorded successfully.',
                    'donation_id' => $donation_stmt->insert_id
                ]);

            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }

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
        $donor_id = $_GET['donor_id'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        $sql = "SELECT d.*, dn.name as donor_name, dn.email as donor_email,
                       DATE_FORMAT(d.donation_date, '%Y-%m-%d') as formatted_date
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

        $sql .= " ORDER BY d.donation_date DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $donations = [];
        while ($row = $result->fetch_assoc()) {
            $donations[] = $row;
        }

        echo json_encode([
            'success' => true,
            'donations' => $donations,
            'count' => count($donations)
        ]);

        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching donations: ' . $e->getMessage()
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
