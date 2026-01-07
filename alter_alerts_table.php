<?php
require_once 'config.php';

try {
    $pdo = $conn;

    // Add columns if they don't exist
    $columns = [
        "ADD COLUMN pregnancy_week VARCHAR(50) DEFAULT NULL",
        "ADD COLUMN red_pixel FLOAT DEFAULT 0",
        "ADD COLUMN green_pixel FLOAT DEFAULT 0",
        "ADD COLUMN blue_pixel FLOAT DEFAULT 0",
        "ADD COLUMN hb_level FLOAT DEFAULT 0",
        "ADD COLUMN systolic INT DEFAULT 0",
        "ADD COLUMN diastolic INT DEFAULT 0",
        "ADD COLUMN fetal_weight INT DEFAULT 0"
    ];

    foreach ($columns as $col) {
        try {
            $sql = "ALTER TABLE doctor_alerts $col";
            $pdo->exec($sql);
            echo "Added column: $col<br>";
        } catch (PDOException $e) {
            // Ignore error if column exists (Code 1060: Duplicate column name)
            if ($e->errorInfo[1] == 1060) {
                echo "Column already exists: $col<br>";
            } else {
                echo "Error adding column: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "Migration completed successfully.";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
