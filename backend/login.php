<?php
// login.php - Enhanced with security features
session_start();
include 'db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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
        $stmt = $conn->prepare("SELECT id, name, password, verified, blood_group FROM donors WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (verifyPassword($password, $user['password'])) {
                // Check if user is verified
                if (!$user['verified']) {
                    throw new Exception("Account not yet verified by admin");
                }

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $email;
                $_SESSION['blood_group'] = $user['blood_group'];
                $_SESSION['logged_in'] = true;

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $email,
                        'blood_group' => $user['blood_group']
                    ]
                ]);
            } else {
                throw new Exception("Invalid credentials");
            }
        } else {
            throw new Exception("Invalid credentials");
        }

        $stmt->close();

    } catch (Exception $e) {
        http_response_code(401);
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
?>