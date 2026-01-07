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
        $query = "SELECT id, username, email, full_name, role, doctor_id_code, hospital_name, specialist, experience_years, profile_image, created_at 
                  FROM users 
                  WHERE username = :username AND role = 'doctor' 
                  LIMIT 1";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode([
                "status" => "success",
                "doctor" => $doctor
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Doctor not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
