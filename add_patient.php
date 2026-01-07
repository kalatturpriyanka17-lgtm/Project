<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        $data = $_POST;
    }

    $username = isset($data['username']) ? trim($data['username']) : '';
    $password = isset($data['password']) ? trim($data['password']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $full_name = isset($data['full_name']) ? trim($data['full_name']) : '';
    $patient_id_code = isset($data['patient_id_code']) ? trim($data['patient_id_code']) : '';
    $pregnancy_week = isset($data['pregnancy_week']) ? trim($data['pregnancy_week']) : '';
    $mobile_number = isset($data['mobile_number']) ? trim($data['mobile_number']) : '';
    $health_issues = isset($data['health_issues']) ? trim($data['health_issues']) : '';

    // Ensure added_by is captured, or fallback to 'admin' if empty (temporary fix for robustness)
    $added_by = isset($data['added_by']) && !empty($data['added_by']) ? trim($data['added_by']) : 'admin';
    // Log for debugging
    file_put_contents('debug_add_patient.txt', "Adding patient: $username, Added by: $added_by\n", FILE_APPEND);

    if (empty($username) || empty($password) || empty($email) || empty($full_name) || empty($patient_id_code)) {
        echo json_encode(["status" => "error", "message" => "Required fields (username, password, email, name, patient ID) are missing"]);
        exit;
    }

    try {
        // Check if username exists
        $check_query = "SELECT id FROM users WHERE username = :username";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            echo json_encode(["status" => "error", "message" => "Username already exists"]);
            exit;
        }

        $query = "INSERT INTO users (username, password, email, role, full_name, patient_id_code, pregnancy_week, mobile_number, health_issues, added_by) 
                  VALUES (:username, :password, :email, 'patient', :full_name, :patient_id_code, :pregnancy_week, :mobile_number, :health_issues, :added_by)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':patient_id_code', $patient_id_code);
        $stmt->bindParam(':pregnancy_week', $pregnancy_week);
        $stmt->bindParam(':mobile_number', $mobile_number);
        $stmt->bindParam(':health_issues', $health_issues);
        $stmt->bindParam(':added_by', $added_by);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Patient registered successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to register patient"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
