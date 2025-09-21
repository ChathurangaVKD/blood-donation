<?php
// login.php - Enhanced with security features and proper session handling
// Configure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600); // 1 hour session lifetime
ini_set('session.cookie_lifetime', 3600);

session_start();
include 'db.php';

// Set proper headers for session handling
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

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

            // Verify password using the function from db.php
            if (verifyPassword($password, $user['password'])) {
                // Clear any existing session data and regenerate session ID
                session_regenerate_id(true);

                // Set session variables with complete user data
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['blood_group'] = $user['blood_group'];
                $_SESSION['location'] = $user['location'];
                $_SESSION['contact'] = $user['contact'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();

                // Force session write
                session_write_close();
                session_start();

                // Debug: Log session data
                error_log("Login successful - Session ID: " . session_id());
                error_log("Session data after login: " . print_r($_SESSION, true));

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful! Welcome back to BloodLink.',
                    'user' => [
                        'id' => (int)$user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'blood_group' => $user['blood_group'],
                        'location' => $user['location'],
                        'contact' => $user['contact']
                    ],
                    'session_id' => session_id()
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
        'message' => 'Method not allowed'
    ]);
}
?>