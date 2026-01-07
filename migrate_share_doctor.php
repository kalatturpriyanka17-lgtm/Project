<?php
require_once 'config.php';

try {
    // 1. Add added_by column to users table if it doesn't exist
    $col_check = $conn->query("SHOW COLUMNS FROM users LIKE 'added_by'");
    if ($col_check->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN added_by VARCHAR(50)");
        echo "Column 'added_by' added to users table.<br>";
    } else {
        echo "Column 'added_by' already exists.<br>";
    }

    // 2. Create doctor_alerts table
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS doctor_alerts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doctor_username VARCHAR(50) NOT NULL,
        patient_username VARCHAR(50) NOT NULL,
        patient_name VARCHAR(100) NOT NULL,
        patient_id VARCHAR(50) NOT NULL,
        alert_type VARCHAR(50) NOT NULL,
        alert_message TEXT,
        severity VARCHAR(20),
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE
    )";
    $conn->exec($create_table_sql);
    echo "Table 'doctor_alerts' created or already exists.<br>";

    echo "Migration completed successfully.";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
