<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config.php';

// Get posted data
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// If JSON decoding failed, try regular POST data
if (!$data) {
    $data = $_POST;
}

// Validate required fields
if (empty($data['username']) || empty($data['current_password']) || empty($data['new_username']) || empty($data['new_password'])) {
    echo json_encode(array("status" => "error", "message" => "All fields are required."));
    exit;
}

$username = trim($data['username']);
$currentPassword = trim($data['current_password']);
$newUsername = trim($data['new_username']);
$newPassword = trim($data['new_password']);

try {
    // First, verify current credentials
    $query = "SELECT id, password FROM users WHERE username = :username LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(array("status" => "error", "message" => "User not found."));
        exit;
    }

    // Verify current password
    if ($user['password'] !== $currentPassword) {
        echo json_encode(array("status" => "error", "message" => "Invalid current password."));
        exit;
    }

    // Check if new username is already taken by another user
    if ($newUsername !== $username) {
        $checkQuery = "SELECT id FROM users WHERE username = :new_username AND id != :current_id LIMIT 1";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':new_username', $newUsername);
        $checkStmt->bindParam(':current_id', $user['id']);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(array("status" => "error", "message" => "The new username is already taken."));
            exit;
        }
    }

    // Update credentials
    $updateQuery = "UPDATE users SET username = :new_username, password = :new_password WHERE id = :id";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':new_username', $newUsername);
    $updateStmt->bindParam(':new_password', $newPassword);
    $updateStmt->bindParam(':id', $user['id']);

    if ($updateStmt->execute()) {
        echo json_encode(array(
            "status" => "success",
            "message" => "Credentials updated successfully. Please login with your new credentials.",
            "user" => array(
                "username" => $newUsername
            )
        ));
    } else {
        echo json_encode(array("status" => "error", "message" => "Unable to update credentials."));
    }
} catch (PDOException $e) {
    echo json_encode(array("status" => "error", "message" => "Database error: " . $e->getMessage()));
}
?>
