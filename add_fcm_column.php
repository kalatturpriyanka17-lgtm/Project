<?php
require_once 'config.php';

try {
    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM users LIKE 'fcm_token'");
    
    if ($check->rowCount() == 0) {
        // Add the column
        $sql = "ALTER TABLE users ADD COLUMN fcm_token TEXT DEFAULT NULL";
        $conn->exec($sql);
        echo "<h3>✅ SUCCESS: 'fcm_token' column added to users table.</h3>";
    } else {
        echo "<h3>ℹ️ Column 'fcm_token' already exists. Good.</h3>";
    }

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
