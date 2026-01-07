<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS fetal_growth_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        gestational_age INT NOT NULL,
        fetal_weight INT NOT NULL,
        severity VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (username)
    )";
    
    $conn->exec($sql);
    echo json_encode(["status" => "success", "message" => "fetal_growth_history table created successfully"]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]);
}
?>
