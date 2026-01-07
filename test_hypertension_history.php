<?php
header("Content-Type: application/json");
require_once 'config.php';

// Test script to check what's in the database
try {
    // Get all hypertension records
    $stmt = $conn->prepare("SELECT username, systolic, diastolic, severity, created_at FROM hypertension_records ORDER BY created_at DESC LIMIT 50");
    $stmt->execute();
    $allRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get count by username
    $stmt2 = $conn->prepare("SELECT username, COUNT(*) as count FROM hypertension_records GROUP BY username");
    $stmt2->execute();
    $counts = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "status" => "success",
        "total_records" => count($allRecords),
        "records" => $allRecords,
        "counts_by_user" => $counts
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "DB Error: " . $e->getMessage()
    ]);
}
?>
