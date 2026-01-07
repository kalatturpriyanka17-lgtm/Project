<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $username = $_GET['username'] ?? '';

    if (empty($username)) {
        echo json_encode(["status" => "error", "message" => "Username required"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM prescriptions WHERE patient_username = :username ORDER BY timestamp DESC");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "prescriptions" => $prescriptions]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only GET requests are allowed"]);
}
?>
