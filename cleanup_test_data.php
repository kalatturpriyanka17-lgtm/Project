<?php
header("Content-Type: application/json");
require_once 'config.php';

$username = $_GET['username'] ?? '';

if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Missing username"]);
    exit;
}

try {
    // Delete all hypertension records for the specific user
    // This allows them to "start from now" after previous test sessions
    $stmt = $conn->prepare("DELETE FROM hypertension_records WHERE username = :u");
    $stmt->bindParam(':u', $username);
    $stmt->execute();

    echo json_encode([
        "status" => "success", 
        "message" => "All hypertension records for '$username' have been cleared. You can now start fresh."
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
}
?>
