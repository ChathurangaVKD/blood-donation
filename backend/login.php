<?php
// login.php - Enhanced with security features
session_start();
include 'db.php';

// Password verification function
function verifyPassword($password, $hash) {
    // If hash looks like bcrypt, use password_verify
    if (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0 || strpos($hash, '$2x$') === 0) {
        return password_verify($password, $hash);
    }
    // For plain text passwords (development only), compare directly
    return $password === $hash;
}

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and sanitize input data
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        // Validate required fields
        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Get user data including password hash
        $stmt = $conn->prepare("SELECT id, name, email, password, blood_group, location, contact FROM donors WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (verifyPassword($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['blood_group'] = $user['blood_group'];
                $_SESSION['logged_in'] = true;

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful! Welcome back to BloodLink.',
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'blood_group' => $user['blood_group'],
                        'location' => $user['location'],
                        'contact' => $user['contact']
                    ]
                ]);
            } else {
                // Add a small delay to prevent brute force attacks
                sleep(1);
                throw new Exception("Invalid email or password");
            }
        } else {
            // Add a small delay to prevent user enumeration
            sleep(1);
            throw new Exception("Invalid email or password");
        }

        $stmt->close();

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error_code' => 'LOGIN_FAILED'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use POST method.',
        'error_code' => 'METHOD_NOT_ALLOWED'
    ]);
}

$conn->close();
?>