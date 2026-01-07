<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        $data = $_POST;
    }

    $doctor_username = isset($data['doctor_username']) ? trim($data['doctor_username']) : '';

    if (empty($doctor_username)) {
        echo json_encode(["status" => "error", "message" => "Doctor username is required"]);
        exit;
    }

    try {
        $query = "SELECT id, username, email, full_name, patient_id_code, pregnancy_week, mobile_number, health_issues, created_at 
                  FROM users 
                  WHERE added_by = :doctor_username AND role = 'patient' 
                  ORDER BY created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':doctor_username', $doctor_username);
        $stmt->execute();

        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "patients" => $patients
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
