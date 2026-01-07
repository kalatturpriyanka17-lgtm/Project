<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data) {
        $username = isset($data['username']) ? trim($data['username']) : '';
        $password = isset($data['password']) ? trim($data['password']) : '';
        $role = isset($data['role']) ? trim($data['role']) : '';
    } else {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    }

    if (empty($username) || empty($password) || empty($role)) {
        echo json_encode(["status" => "error", "message" => "Please provide username, password and role"]);
        exit;
    }

    try {
        // First, check if the username exists at all
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if the role matches
            if ($user['role'] !== $role) {
                echo json_encode(["status" => "error", "message" => "Role mismatch: User exists but is not a " . $role]);
                exit;
            }

            // Simple password comparison (In production, use password_verify)
            if ($password === $user['password']) {
                unset($user['password']); // Don't send password back
                echo json_encode([
                    "status" => "success",
                    "message" => "Login successful",
                    "user" => $user
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid password"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "User not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
