<?php
// register.php - Enhanced with security features
include 'db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and sanitize input data
        $name = sanitizeInput($_POST['name'] ?? '');
        $age = (int)($_POST['age'] ?? 0);
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $blood_group = sanitizeInput($_POST['blood_group'] ?? '');
        $contact = sanitizeInput($_POST['contact'] ?? '');
        $location = sanitizeInput($_POST['location'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        // Validate required fields
        if (empty($name) || empty($age) || empty($gender) || empty($blood_group) ||
            empty($contact) || empty($location) || empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validate age
        if ($age < 18 || $age > 65) {
            throw new Exception("Age must be between 18 and 65");
        }

        // Validate blood group
        $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        if (!in_array($blood_group, $valid_blood_groups)) {
            throw new Exception("Invalid blood group");
        }

        // Validate gender
        $valid_genders = ['Male', 'Female', 'Other'];
        if (!in_array($gender, $valid_genders)) {
            throw new Exception("Invalid gender");
        }

        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM donors WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Email already registered");
        }

        // Hash password
        $hashed_password = hashPassword($password);

        // Insert new donor
        $stmt = $conn->prepare("INSERT INTO donors (name, age, gender, blood_group, contact, location, email, password, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sissssss", $name, $age, $gender, $blood_group, $contact, $location, $email, $hashed_password);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful. Awaiting admin verification.',
                'donor_id' => $conn->insert_id
            ]);
        } else {
            throw new Exception("Registration failed: " . $stmt->error);
        }

        $stmt->close();
        $check_stmt->close();

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
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

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}
?>