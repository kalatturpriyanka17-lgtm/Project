<?php
header("Content-Type: application/json");
require_once 'config.php';

$username = $_GET['username'] ?? '';

if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Missing username"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, username, role, full_name FROM users WHERE username = :u");
    $stmt->execute(['u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(["status" => "success", "user" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
