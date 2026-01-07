<?php
require_once 'config.php';

echo "--- PATIENT ALERTS (Last 10) ---\n";
try {
    $stmt = $conn->query("SELECT * FROM patient_alerts ORDER BY created_at DESC LIMIT 10");
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($alerts);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "\n--- USERS (Last 5) ---\n";
try {
    $stmt = $conn->query("SELECT id, username, role FROM users ORDER BY id DESC LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
