<?php
require_once 'config.php';

echo "--- DOCTOR ALERTS (Last 10) ---\n";
try {
    $stmt = $conn->query("SELECT * FROM doctor_alerts ORDER BY timestamp DESC LIMIT 10");
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($alerts);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
