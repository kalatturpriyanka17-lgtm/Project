<?php
require_once 'config.php';

echo "<h1>Users List</h1>";
echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Role</th><th>Added By</th></tr>";

try {
    $stmt = $conn->query("SELECT id, username, role, added_by FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . ($row['added_by'] ? $row['added_by'] : "NULL") . "</td>";
        echo "</tr>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
echo "</table>";
?>
