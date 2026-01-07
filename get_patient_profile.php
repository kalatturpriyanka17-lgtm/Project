<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        $data = $_POST;
    }

    $username = isset($data['username']) ? trim($data['username']) : '';

    if (empty($username)) {
        echo json_encode(["status" => "error", "message" => "Username is required"]);
        exit;
    }

    try {
        $query = "SELECT username, email, full_name, patient_id_code, pregnancy_week, mobile_number, health_issues, caretaker_name, caretaker_relationship, caretaker_mobile, profile_image 
                  FROM users 
                  WHERE username = :username AND role = 'patient' 
                  LIMIT 1";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode([
                "status" => "success",
                "patient" => $patient
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Patient not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
