<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data) {
        $username = isset($data['username']) ? $data['username'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $email = isset($data['email']) ? $data['email'] : '';
        $role = isset($data['role']) ? $data['role'] : '';
        $full_name = isset($data['full_name']) ? $data['full_name'] : '';
    } else {
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : '';
        $full_name = isset($_POST['full_name']) ? $_POST['full_name'] : '';
    }

    if (empty($username) || empty($password) || empty($email) || empty($role)) {
        echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
        exit;
    }

    // BLOCK ADMIN REGISTRATION
    if ($role === 'admin') {
        echo json_encode(["status" => "error", "message" => "Admin accounts cannot be created via the application"]);
        exit;
    }

    try {
        // Check if username exists
        $check_query = "SELECT id FROM users WHERE username = :username";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            echo json_encode(["status" => "error", "message" => "Username already exists"]);
            exit;
        }

        $query = "INSERT INTO users (username, password, email, role, full_name) VALUES (:username, :password, :email, :role, :full_name)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':full_name', $full_name);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Registration successful"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Registration failed"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
