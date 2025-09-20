<?php
// register.php - Enhanced donor registration with proper validation and responses
include 'db.php';

// Helper function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Helper function to hash passwords
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

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
        $medical_history = sanitizeInput($_POST['medical_history'] ?? '');

        // Enhanced validation with specific error messages
        $errors = [];

        // Validate required fields
        if (empty($name)) $errors[] = "Full name is required";
        if (empty($age)) $errors[] = "Age is required";
        if (empty($gender)) $errors[] = "Gender is required";
        if (empty($blood_group)) $errors[] = "Blood group is required";
        if (empty($contact)) $errors[] = "Phone number is required";
        if (empty($location)) $errors[] = "Location is required";
        if (empty($email)) $errors[] = "Email address is required";
        if (empty($password)) $errors[] = "Password is required";

        // Validate name (minimum 2 characters, letters and spaces only)
        if (!empty($name) && (strlen($name) < 2 || !preg_match("/^[a-zA-Z\s\.]+$/", $name))) {
            $errors[] = "Name must be at least 2 characters and contain only letters";
        }

        // Validate email format
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }

        // Validate age
        if ($age > 0 && ($age < 18 || $age > 65)) {
            $errors[] = "Age must be between 18 and 65 years";
        }

        // Validate phone number
        if (!empty($contact) && !preg_match("/^[\+]?[\d\s\-\(\)]{10,}$/", $contact)) {
            $errors[] = "Please enter a valid phone number (minimum 10 digits)";
        }

        // Validate password
        if (!empty($password) && strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }

        // Validate blood group
        $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        if (!empty($blood_group) && !in_array($blood_group, $valid_blood_groups)) {
            $errors[] = "Please select a valid blood group";
        }

        // Validate gender
        $valid_genders = ['Male', 'Female', 'Other'];
        if (!empty($gender) && !in_array($gender, $valid_genders)) {
            $errors[] = "Please select a valid gender";
        }

        // Validate location
        if (!empty($location) && (strlen($location) < 2 || !preg_match("/^[a-zA-Z0-9\s\.\-,]+$/", $location))) {
            $errors[] = "Location must be at least 2 characters and contain valid characters";
        }

        // Check if email already exists
        if (empty($errors) && !empty($email)) {
            $check_stmt = $conn->prepare("SELECT id FROM donors WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Email address is already registered. Please use a different email or login to your existing account.";
            }
            $check_stmt->close();
        }

        // If validation errors exist, return them
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Registration failed due to validation errors',
                'errors' => $errors
            ]);
            exit;
        }

        // Hash password
        $hashed_password = hashPassword($password);

        // Insert new donor with verified status true for immediate availability
        $stmt = $conn->prepare("INSERT INTO donors (name, age, gender, blood_group, contact, location, email, password, medical_history, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sissssss", $name, $age, $gender, $blood_group, $contact, $location, $email, $hashed_password, $medical_history);

        if (!$stmt->execute()) {
            throw new Exception("Failed to register donor: " . $stmt->error);
        }

        $donor_id = $conn->insert_id;
        $stmt->close();

        // Return success response with donor information
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Welcome to BloodLink.',
            'donor_id' => $donor_id,
            'data' => [
                'name' => $name,
                'email' => $email,
                'blood_group' => $blood_group,
                'location' => $location,
                'verified' => true
            ]
        ]);

    } catch (Exception $e) {
        // Log error for debugging
        error_log("Registration error: " . $e->getMessage());

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An internal error occurred during registration. Please try again.',
            'error_code' => 'REGISTRATION_ERROR',
            'debug_info' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use POST method.'
    ]);
}

$conn->close();
?>