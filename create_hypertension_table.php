<?php
header("Content-Type: application/json");
require_once 'config.php';

// Create hypertension_records table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `hypertension_records` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(100) NOT NULL,
      `systolic` int(11) NOT NULL,
      `diastolic` int(11) NOT NULL,
      `blood_sugar` float DEFAULT NULL,
      `body_temp` float DEFAULT NULL,
      `heart_rate` int(11) DEFAULT NULL,
      `severity` varchar(100) NOT NULL,
      `symptoms` text,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `username` (`username`),
      KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->exec($sql);
    
    echo json_encode([
        "status" => "success",
        "message" => "Table hypertension_records created successfully"
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error creating table: " . $e->getMessage()
    ]);
}
?>
