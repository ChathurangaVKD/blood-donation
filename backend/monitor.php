<?php
// monitor.php - Monitor blood requests and available donors (Logged users only)
// Configure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Lax');

session_start();
include 'db.php';

// Set proper headers for session handling
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Debug session information
error_log("Monitor.php - Session ID: " . session_id());
error_log("Monitor.php - Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Please login to access this page',
        'redirect' => 'login.html',
        'debug' => [
            'session_id' => session_id(),
            'logged_in_set' => isset($_SESSION['logged_in']),
            'logged_in_value' => $_SESSION['logged_in'] ?? null,
            'user_email_set' => isset($_SESSION['user_email']),
            'session_data' => $_SESSION
        ]
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $action = $_GET['action'] ?? '';
        $user_email = $_SESSION['user_email']; // Get email from session

        if ($action === 'user_data') {
            // Get user's complete data including requests and matching donors

            // First, get user's blood requests
            $stmt = $conn->prepare("
                SELECT r.*, 
                       COUNT(rf.id) as fulfillments_count,
                       SUM(rf.units_fulfilled) as units_fulfilled
                FROM requests r 
                LEFT JOIN request_fulfillments rf ON r.id = rf.request_id
                WHERE r.requester_email = ? 
                GROUP BY r.id
                ORDER BY r.created_at DESC
            ");
            $stmt->bind_param("s", $user_email);
            $stmt->execute();
            $result = $stmt->get_result();

            $requests = [];
            $requested_blood_types = [];

            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
                // Collect unique blood types requested by this user
                if (!in_array($row['blood_group'], $requested_blood_types)) {
                    $requested_blood_types[] = $row['blood_group'];
                }
            }

            // Get matching donors for all requested blood types
            $matching_donors = [];
            foreach ($requested_blood_types as $blood_type) {
                $compatible_types = getCompatibleBloodTypes($blood_type);
                $placeholders = str_repeat('?,', count($compatible_types) - 1) . '?';

                $donor_stmt = $conn->prepare("
                    SELECT d.id, d.name, d.blood_group, d.location, d.contact, d.last_donation_date,
                           CASE 
                               WHEN d.last_donation_date IS NULL THEN 'Available'
                               WHEN DATEDIFF(CURDATE(), d.last_donation_date) >= 56 THEN 'Available'
                               ELSE CONCAT('Available in ', 56 - DATEDIFF(CURDATE(), d.last_donation_date), ' days')
                           END as availability_status,
                           ? as requested_blood_type
                    FROM donors d 
                    WHERE d.blood_group IN ($placeholders) 
                    AND d.verified = 1
                    AND d.email != ?
                    ORDER BY 
                            CASE WHEN d.last_donation_date IS NULL THEN 0
                                 WHEN DATEDIFF(CURDATE(), d.last_donation_date) >= 56 THEN 1
                                 ELSE 2 
                            END,
                            d.location, d.name
                ");

                $params = array_merge([$blood_type], $compatible_types, [$user_email]);
                $types = 's' . str_repeat('s', count($compatible_types)) . 's';

                $donor_stmt->bind_param($types, ...$params);
                $donor_stmt->execute();
                $donor_result = $donor_stmt->get_result();

                while ($donor = $donor_result->fetch_assoc()) {
                    $matching_donors[] = $donor;
                }
            }

            // Get blood inventory for requested types
            $inventory = [];
            if (!empty($requested_blood_types)) {
                $inv_placeholders = str_repeat('?,', count($requested_blood_types) - 1) . '?';
                $inv_stmt = $conn->prepare("
                    SELECT i.blood_group, i.location, COUNT(*) as units_available,
                           MIN(i.expiry_date) as earliest_expiry
                    FROM inventory i 
                    WHERE i.blood_group IN ($inv_placeholders) 
                    AND i.status = 'available' 
                    AND i.expiry_date > CURDATE()
                    GROUP BY i.blood_group, i.location
                    ORDER BY i.blood_group, i.location
                ");
                $inv_stmt->bind_param(str_repeat('s', count($requested_blood_types)), ...$requested_blood_types);
                $inv_stmt->execute();
                $inv_result = $inv_stmt->get_result();

                while ($inv_row = $inv_result->fetch_assoc()) {
                    $inventory[] = $inv_row;
                }
            }

            echo json_encode([
                'success' => true,
                'user' => [
                    'name' => $_SESSION['user_name'],
                    'email' => $_SESSION['user_email'],
                    'blood_group' => $_SESSION['blood_group']
                ],
                'requests' => $requests,
                'requested_blood_types' => $requested_blood_types,
                'matching_donors' => $matching_donors,
                'inventory' => $inventory
            ]);

        } elseif ($action === 'available_donors') {
            // Get available donors for a specific blood type (kept for manual search)
            $blood_group = $_GET['blood_group'] ?? '';
            $location = $_GET['location'] ?? '';

            if (empty($blood_group)) {
                throw new Exception("Blood group is required");
            }

            $compatible_types = getCompatibleBloodTypes($blood_group);
            $placeholders = str_repeat('?,', count($compatible_types) - 1) . '?';

            $query = "
                SELECT d.id, d.name, d.blood_group, d.location, d.contact, d.last_donation_date,
                       CASE 
                           WHEN d.last_donation_date IS NULL THEN 'Available'
                           WHEN DATEDIFF(CURDATE(), d.last_donation_date) >= 56 THEN 'Available'
                           ELSE CONCAT('Available in ', 56 - DATEDIFF(CURDATE(), d.last_donation_date), ' days')
                       END as availability_status
                FROM donors d 
                WHERE d.blood_group IN ($placeholders) 
                AND d.verified = 1
                AND d.email != ?
            ";

            $params = array_merge($compatible_types, [$user_email]);
            $types = str_repeat('s', count($compatible_types)) . 's';

            if (!empty($location)) {
                $query .= " AND d.location LIKE ?";
                $params[] = "%$location%";
                $types .= 's';
            }

            $query .= " ORDER BY 
                        CASE WHEN d.last_donation_date IS NULL THEN 0
                             WHEN DATEDIFF(CURDATE(), d.last_donation_date) >= 56 THEN 1
                             ELSE 2 
                        END,
                        d.location, d.name";

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $donors = [];
            while ($row = $result->fetch_assoc()) {
                $donors[] = $row;
            }

            echo json_encode([
                'success' => true,
                'donors' => $donors,
                'compatible_types' => $compatible_types
            ]);

        } else {
            throw new Exception("Invalid action specified");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
}

// Function to get compatible blood types for transfusion
function getCompatibleBloodTypes($requested_type) {
    $compatibility = [
        'A+' => ['A+', 'A-', 'O+', 'O-'],
        'A-' => ['A-', 'O-'],
        'B+' => ['B+', 'B-', 'O+', 'O-'],
        'B-' => ['B-', 'O-'],
        'AB+' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], // Universal recipient
        'AB-' => ['A-', 'B-', 'AB-', 'O-'],
        'O+' => ['O+', 'O-'],
        'O-' => ['O-'] // Universal donor, but can only receive O-
    ];

    return $compatibility[$requested_type] ?? [$requested_type];
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
