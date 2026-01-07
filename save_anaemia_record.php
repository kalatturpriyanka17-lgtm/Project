<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $red_pixel = isset($_POST['red_pixel']) ? floatval($_POST['red_pixel']) : 0;
    $green_pixel = isset($_POST['green_pixel']) ? floatval($_POST['green_pixel']) : 0;
    $blue_pixel = isset($_POST['blue_pixel']) ? floatval($_POST['blue_pixel']) : 0;
    $hb_level = isset($_POST['hb_level']) ? floatval($_POST['hb_level']) : 0;
    $severity = isset($_POST['severity']) ? trim($_POST['severity']) : '';
    $symptoms = isset($_POST['symptoms']) ? trim($_POST['symptoms']) : '';

    if (empty($username) || empty($severity)) {
        echo json_encode(["status" => "error", "message" => "Missing required data"]);
        exit;
    }

    try {
        $query = "INSERT INTO anaemia_history (username, red_pixel, green_pixel, blue_pixel, hb_level, severity, symptoms) 
                  VALUES (:username, :red_pixel, :green_pixel, :blue_pixel, :hb_level, :severity, :symptoms)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':red_pixel', $red_pixel);
        $stmt->bindParam(':green_pixel', $green_pixel);
        $stmt->bindParam(':blue_pixel', $blue_pixel);
        $stmt->bindParam(':hb_level', $hb_level);
        $stmt->bindParam(':severity', $severity);
        $stmt->bindParam(':symptoms', $symptoms);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Record saved successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save record"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
