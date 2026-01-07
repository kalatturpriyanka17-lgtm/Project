<?php
require_once 'config.php';

header("Content-Type: text/html");

echo "<h1>Database Check</h1>";

try {
    // 1. Check Table Exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'hypertension_records'");
    if ($tableCheck->rowCount() == 0) {
        echo "<h2 style='color:red'>TABLE MISSING! 'hypertension_records' does not exist.</h2>";
        exit;
    }

    // 2. Check Row Count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM hypertension_records");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $row['count'];
    echo "<h2>Total Records: $count</h2>";
    
    if ($count > 0) {
        $stmt = $conn->query("SELECT * FROM hypertension_records ORDER BY created_at DESC LIMIT 5");
        echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Username</th><th>Sys</th><th>Dia</th><th>Severity</th><th>Time</th></tr>";
        while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
             echo "<tr>";
             echo "<td>{$r['id']}</td>";
             echo "<td>{$r['username']}</td>";
             echo "<td>{$r['systolic']}</td>";
             echo "<td>{$r['diastolic']}</td>";
             echo "<td>{$r['severity']}</td>";
             echo "<td>{$r['created_at']}</td>";
             echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h3 style='color:orange'>Table is empty. Please use the App to Save a Record.</h3>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
