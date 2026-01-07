<?php
header("Content-Type: application/json");
require_once 'config.php';

$username = $_GET['username'] ?? '';

if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Missing username"]);
    exit;
}

try {
    // We want to combine all history tables and sort by date
    // Standardizing columns: type, status/severity, sub_info, created_at
    
    $query = "
        (SELECT 'anaemia' as type, severity as status, CONCAT(hb_level, ' g/dL') as sub_info, created_at 
         FROM anaemia_history 
         WHERE username = :u1)
        UNION ALL
        (SELECT 'hypertension' as type, severity as status, CONCAT(systolic, '/', diastolic) as sub_info, created_at 
         FROM hypertension_records 
         WHERE username = :u2)
        UNION ALL
        (SELECT 'fetal_growth' as type, severity as status, CONCAT(gestational_age, ' Weeks') as sub_info, created_at 
         FROM fetal_growth_history 
         WHERE username = :u3)
        ORDER BY created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':u1', $username);
    $stmt->bindParam(':u2', $username);
    $stmt->bindParam(':u3', $username);
    $stmt->execute();
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "status" => "success",
        "history" => $history
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
}
?>
