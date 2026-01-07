<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';

    if (empty($username)) {
        echo json_encode(["status" => "error", "message" => "Missing username"]);
        exit;
    }

    try {
        $query = "SELECT * FROM anaemia_history WHERE username = :username ORDER BY created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            "status" => "success",
            "message" => "History retrieved successfully",
            "anaemia_history" => $history
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
