<?php
header("Content-Type: application/json");
require_once 'config.php';

try {
    $anaemia = $conn->query("SELECT DISTINCT username FROM anaemia_history")->fetchAll(PDO::FETCH_COLUMN);
    $hyper = $conn->query("SELECT DISTINCT username FROM hypertension_records")->fetchAll(PDO::FETCH_COLUMN);
    $fetal = $conn->query("SELECT DISTINCT username FROM fetal_growth_history")->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        "status" => "success",
        "active_usernames" => [
            "anaemia" => $anaemia,
            "hypertension" => $hyper,
            "fetal_growth" => $fetal
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
