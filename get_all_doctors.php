<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT id, username, email, full_name, doctor_id_code, hospital_name, specialist, experience_years, created_at 
                  FROM users 
                  WHERE role = 'doctor' 
                  ORDER BY created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "status" => "success",
            "count" => count($doctors),
            "doctors" => $doctors
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
