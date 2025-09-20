<?php
// request.php - Enhanced blood request handling with improved validation and responses
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

        // Enhanced validation with specific error messages
        $errors = [];

        // Validate required fields
        if (empty($requester_name)) $errors[] = "Patient name is required";
        if (empty($requester_contact)) $errors[] = "Contact number is required";
        if (empty($requester_email)) $errors[] = "Email address is required";
        if (empty($blood_group)) $errors[] = "Blood type is required";
        if (empty($location)) $errors[] = "Location is required";
        if (empty($urgency)) $errors[] = "Urgency level is required";
        if (empty($hospital)) $errors[] = "Hospital name is required";
        if (empty($required_date)) $errors[] = "Required date is required";

        // Validate name (minimum 2 characters, letters and spaces only)
        if (!empty($requester_name) && (strlen($requester_name) < 2 || !preg_match("/^[a-zA-Z\s\.]+$/", $requester_name))) {
            $errors[] = "Patient name must be at least 2 characters and contain only letters";
        }

        // Validate email format
        if (!empty($requester_email) && !filter_var($requester_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }

        // Validate phone number
        if (!empty($requester_contact) && !preg_match("/^[\+]?[\d\s\-\(\)]{10,}$/", $requester_contact)) {
            $errors[] = "Contact number must be at least 10 digits";
        }

        // Validate blood group
        $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        if (!empty($blood_group) && !in_array($blood_group, $valid_blood_groups)) {
            $errors[] = "Please select a valid blood type";
        }

        // Validate urgency
        $valid_urgency = ['Low', 'Medium', 'High', 'Critical'];
        if (!empty($urgency) && !in_array($urgency, $valid_urgency)) {
            $errors[] = "Please select a valid urgency level";
        }

        // Validate date
        if (!empty($required_date)) {
            $date = DateTime::createFromFormat('Y-m-d', $required_date);
            if (!$date || $date->format('Y-m-d') !== $required_date) {
                $errors[] = "Please enter a valid date";
            } elseif ($date < new DateTime()) {
                $errors[] = "Required date cannot be in the past";
            } elseif ($date > (new DateTime())->modify('+1 year')) {
                $errors[] = "Required date cannot be more than 1 year in the future";
            }
        }

        // Validate units needed
        if ($units_needed < 1 || $units_needed > 10) {
            $errors[] = "Units needed must be between 1 and 10";
        }

        // Validate hospital name
        if (!empty($hospital) && (strlen($hospital) < 3 || !preg_match("/^[a-zA-Z0-9\s\.\-,]+$/", $hospital))) {
            $errors[] = "Hospital name must be at least 3 characters and contain valid characters";
        }

        // Validate location
        if (!empty($location) && (strlen($location) < 2 || !preg_match("/^[a-zA-Z0-9\s\.\-,]+$/", $location))) {
            $errors[] = "Location must be at least 2 characters and contain valid characters";
        }

        // Check for duplicate recent requests (same email, blood type within 24 hours)
        if (empty($errors)) {
            $duplicate_check = $conn->prepare("SELECT id FROM requests WHERE requester_email = ? AND blood_group = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status = 'pending'");
            $duplicate_check->bind_param("ss", $requester_email, $blood_group);
            $duplicate_check->execute();
            $duplicate_result = $duplicate_check->get_result();

            if ($duplicate_result->num_rows > 0) {
                $errors[] = "You already have a pending request for this blood type within the last 24 hours";
            }
            $duplicate_check->close();
        }

        // If validation errors exist, return them
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
                'field_errors' => $errors // For backward compatibility
            ]);
            exit;
        }

        // Insert blood request (no transaction needed for single insert)
        $stmt = $conn->prepare("INSERT INTO requests (requester_name, requester_contact, requester_email, blood_group, location, urgency, hospital, required_date, units_needed, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssis", $requester_name, $requester_contact, $requester_email, $blood_group, $location, $urgency, $hospital, $required_date, $units_needed, $notes);

        if (!$stmt->execute()) {
            throw new Exception("Failed to save request: " . $stmt->error);
        }

        $request_id = $conn->insert_id;
        $stmt->close();

        // Calculate estimated response time based on urgency
        $response_times = [
            'Critical' => '1 hour',
            'High' => '6 hours',
            'Medium' => '24 hours',
            'Low' => '72 hours'
        ];

        // Return success response with detailed information
        echo json_encode([
            'success' => true,
            'message' => 'Blood request submitted successfully',
            'request_id' => $request_id,
            'data' => [
                'patient_name' => $requester_name,
                'blood_type' => $blood_group,
                'urgency' => $urgency,
                'estimated_response_time' => $response_times[$urgency] ?? '24 hours',
                'hospital' => $hospital,
                'location' => $location,
                'required_date' => $required_date,
                'contact_email' => $requester_email,
                'units_needed' => $units_needed
            ]
        ]);

    } catch (Exception $e) {

        // Log error for debugging
        error_log("Blood request error: " . $e->getMessage());

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An internal error occurred while processing your request. Please try again or contact support.',
            'error_code' => 'INTERNAL_ERROR'
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
?>