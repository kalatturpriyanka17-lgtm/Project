<?php
header("Content-Type: application/json");
require_once 'config.php';

// Migration to add anaemia_history table
try {
    $sql = "CREATE TABLE IF NOT EXISTS anaemia_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        red_pixel FLOAT NOT NULL,
        green_pixel FLOAT NOT NULL,
        blue_pixel FLOAT NOT NULL,
        hb_level FLOAT NOT NULL,
        severity VARCHAR(20) NOT NULL,
        symptoms TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (username)
    )";
    
    $conn->exec($sql);
    echo json_encode(["status" => "success", "message" => "Database table 'anaemia_history' updated successfully"]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
