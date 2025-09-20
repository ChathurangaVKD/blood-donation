<?php
// request.php - Enhanced blood request handling
include 'db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and sanitize input data
        $requester_name = sanitizeInput($_POST['requester_name'] ?? '');
        $requester_contact = sanitizeInput($_POST['requester_contact'] ?? '');
        $requester_email = filter_var($_POST['requester_email'] ?? '', FILTER_SANITIZE_EMAIL);
        $blood_group = sanitizeInput($_POST['blood_group'] ?? '');
        $location = sanitizeInput($_POST['location'] ?? '');
        $urgency = sanitizeInput($_POST['urgency'] ?? '');
        $hospital = sanitizeInput($_POST['hospital'] ?? '');
        $required_date = $_POST['required_date'] ?? '';
        $units_needed = (int)($_POST['units_needed'] ?? 1);
        $notes = sanitizeInput($_POST['notes'] ?? '');

        // Validate required fields
        if (empty($requester_name) || empty($requester_contact) || empty($requester_email) ||
            empty($blood_group) || empty($location) || empty($urgency) || empty($hospital) || empty($required_date)) {
            throw new Exception("All required fields must be filled");
        }

        // Validate email format
        if (!filter_var($requester_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validate blood group
        $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        if (!in_array($blood_group, $valid_blood_groups)) {
            throw new Exception("Invalid blood group");
        }

        // Validate urgency
        $valid_urgency = ['Low', 'Medium', 'High', 'Critical'];
        if (!in_array($urgency, $valid_urgency)) {
            throw new Exception("Invalid urgency level");
        }

        // Validate date
        $date = DateTime::createFromFormat('Y-m-d', $required_date);
        if (!$date || $date->format('Y-m-d') !== $required_date) {
            throw new Exception("Invalid date format");
        }

        // Check if date is not in the past
        if ($date < new DateTime()) {
            throw new Exception("Required date cannot be in the past");
        }

        // Validate units needed
        if ($units_needed < 1 || $units_needed > 10) {
            throw new Exception("Units needed must be between 1 and 10");
        }

        // Insert blood request
        $stmt = $conn->prepare("INSERT INTO requests (requester_name, requester_contact, requester_email, blood_group, location, urgency, hospital, required_date, units_needed, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssIs", $requester_name, $requester_contact, $requester_email, $blood_group, $location, $urgency, $hospital, $required_date, $units_needed, $notes);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Blood request submitted successfully.',
                'request_id' => $conn->insert_id
            ]);
        } else {
            throw new Exception("Error submitting request: " . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        // Get query parameters for filtering
        $status = $_GET['status'] ?? 'pending';
        $blood_group = $_GET['blood_group'] ?? '';
        $urgency = $_GET['urgency'] ?? '';
        $location = $_GET['location'] ?? '';

        // Build query with filters
        $sql = "SELECT r.*, DATE_FORMAT(r.required_date, '%Y-%m-%d') as formatted_date, 
                       DATE_FORMAT(r.created_at, '%Y-%m-%d %H:%i') as request_time
                FROM requests r WHERE r.status = ?";
        $params = [$status];
        $types = "s";

        if (!empty($blood_group)) {
            $sql .= " AND r.blood_group = ?";
            $params[] = $blood_group;
            $types .= "s";
        }

        if (!empty($urgency)) {
            $sql .= " AND r.urgency = ?";
            $params[] = $urgency;
            $types .= "s";
        }

        if (!empty($location)) {
            $sql .= " AND r.location LIKE ?";
            $params[] = "%$location%";
            $types .= "s";
        }

        $sql .= " ORDER BY 
                    CASE r.urgency 
                        WHEN 'Critical' THEN 1 
                        WHEN 'High' THEN 2 
                        WHEN 'Medium' THEN 3 
                        WHEN 'Low' THEN 4 
                    END, 
                    r.required_date ASC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }

        echo json_encode([
            'success' => true,
            'requests' => $requests,
            'count' => count($requests)
        ]);

        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching requests: ' . $e->getMessage()
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

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>