<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $token = $_POST['fcm_token'] ?? '';

    if (empty($username) || empty($token)) {
        echo json_encode(["status" => "error", "message" => "Missing username or token"]);
        exit;
    }

    try {
        // Update token in users table (works for both doctors and patients)
        $stmt = $conn->prepare("UPDATE users SET fcm_token = :token WHERE username = :username");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':username', $username);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Token updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update token"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
