<?php
$host = "localhost";
$db_name = "pregsafe_db";
$username = "root";
$password = ""; // Default XAMPP password is empty

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "Connection error: " . $exception->getMessage()]);
    exit;
}
