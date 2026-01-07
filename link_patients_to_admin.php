<?php
require_once 'config.php';

try {
    // 1. Get the admin username (or a default doctor)
    // For this fix, we'll use 'admin' or finding a user with role 'doctor'
    $doctor = 'admin'; // Default fallback
    
    // Find a valid doctor if admin is not a doctor role (though admin can receive alerts in this system design?)
    // Actually, let's find the first user with role 'doctor' or 'admin'
    $stmt = $conn->query("SELECT username FROM users WHERE role IN ('doctor', 'admin') LIMIT 1");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $doctor = $row['username'];
    }

    echo "Linking unassigned patients to doctor: $doctor<br>";

    // 2. Update patients with NULL added_by
    $sql = "UPDATE users SET added_by = :doctor WHERE role = 'patient' AND (added_by IS NULL OR added_by = '')";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':doctor', $doctor);
    $stmt->execute();

    echo "Updated " . $stmt->rowCount() . " patients.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
