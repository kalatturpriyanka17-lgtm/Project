<?php
require_once 'config.php';

try {
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS added_by VARCHAR(50) DEFAULT NULL";
    $conn->exec($sql);
    echo "Column 'added_by' added successfully or already exists.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
