<?php
header("Content-Type: application/json");
require_once 'config.php';

$username = isset($_GET['username']) ? $_GET['username'] : 'jane_doe';

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "user" => $user]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
