<?php
// create_demo_user.php - Create a demo user for testing
include 'db.php';

try {
    // Demo user data
    $demo_email = 'demo@bloodlink.com';
    $demo_password = 'demo123';
    $demo_name = 'Demo User';
    $demo_blood_group = 'O+';
    $demo_location = 'New York, NY';
    $demo_contact = '+1-555-0123';
    $demo_age = 25;
    $demo_gender = 'Male';

    // Check if demo user already exists
    $check_stmt = $conn->prepare("SELECT id FROM donors WHERE email = ?");
    $check_stmt->bind_param("s", $demo_email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Demo user already exists',
            'login_info' => [
                'email' => $demo_email,
                'password' => $demo_password
            ]
        ]);
    } else {
        // Create demo user
        $hashed_password = hashPassword($demo_password);

        $stmt = $conn->prepare("INSERT INTO donors (name, age, gender, blood_group, contact, location, email, password, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sissssss", $demo_name, $demo_age, $demo_gender, $demo_blood_group, $demo_contact, $demo_location, $demo_email, $hashed_password);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            // Create a demo blood request for this user
            $request_stmt = $conn->prepare("INSERT INTO requests (requester_name, requester_contact, requester_email, blood_group, location, urgency, hospital, required_date, units_needed, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $patient_name = "John Doe";
            $urgency = "High";
            $hospital = "City General Hospital";
            $required_date = "2025-09-25";
            $units_needed = 2;
            $notes = "Patient scheduled for emergency surgery. Blood needed urgently.";
            $status = "pending";

            $request_stmt->bind_param("sssssssssss", $patient_name, $demo_contact, $demo_email, $demo_blood_group, $demo_location, $urgency, $hospital, $required_date, $units_needed, $notes, $status);
            $request_stmt->execute();

            echo json_encode([
                'success' => true,
                'message' => 'Demo user created successfully',
                'user_id' => $user_id,
                'login_info' => [
                    'email' => $demo_email,
                    'password' => $demo_password
                ]
            ]);
        } else {
            throw new Exception("Failed to create demo user");
        }
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
