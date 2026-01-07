<?php
header("Content-Type: application/json");
require_once 'config.php';

$username = $_GET['username'] ?? '';

if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Missing username"]);
    exit;
}

try {
    $anaemia = $conn->prepare("SELECT * FROM anaemia_history WHERE username = :u");
    $anaemia->execute(['u' => $username]);
    $anaemia_records = $anaemia->fetchAll(PDO::FETCH_ASSOC);

    $hypertension = $conn->prepare("SELECT * FROM hypertension_records WHERE username = :u");
    $hypertension->execute(['u' => $username]);
    $hypertension_records = $hypertension->fetchAll(PDO::FETCH_ASSOC);

    $fetal = $conn->prepare("SELECT * FROM fetal_growth_history WHERE username = :u");
    $fetal->execute(['u' => $username]);
    $fetal_records = $fetal->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "username" => $username,
        "counts" => [
            "anaemia" => count($anaemia_records),
            "hypertension" => count($hypertension_records),
            "fetal_growth" => count($fetal_records)
        ],
        "data" => [
            "anaemia" => $anaemia_records,
            "hypertension" => $hypertension_records,
            "fetal_growth" => $fetal_records
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
