<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        $data = $_POST;
    }

    $doctor_id = isset($data['doctor_id']) ? trim($data['doctor_id']) : '';
    $username = isset($data['username']) ? trim($data['username']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $full_name = isset($data['full_name']) ? trim($data['full_name']) : '';
    $doctor_id_code = isset($data['doctor_id_code']) ? trim($data['doctor_id_code']) : '';
    $hospital_name = isset($data['hospital_name']) ? trim($data['hospital_name']) : '';
    $specialist = isset($data['specialist']) ? trim($data['specialist']) : '';
    $experience_years = isset($data['experience_years']) ? trim($data['experience_years']) : '';

    if (empty($doctor_id)) {
        echo json_encode(["status" => "error", "message" => "Doctor ID is required"]);
        exit;
    }

    try {
        // Check if the doctor exists
        $checkQuery = "SELECT id FROM users WHERE id = :doctor_id AND role = 'doctor' LIMIT 1";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':doctor_id', $doctor_id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() == 0) {
            echo json_encode(["status" => "error", "message" => "Doctor not found"]);
            exit;
        }

        // Build update query dynamically
        $updateFields = [];
        $params = [':doctor_id' => $doctor_id];

        if (!empty($username)) {
            // Check if username is already taken by another user
            $usernameCheck = "SELECT id FROM users WHERE username = :username AND id != :doctor_id LIMIT 1";
            $usernameStmt = $conn->prepare($usernameCheck);
            $usernameStmt->bindParam(':username', $username);
            $usernameStmt->bindParam(':doctor_id', $doctor_id);
            $usernameStmt->execute();

            if ($usernameStmt->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "Username already exists"]);
                exit;
            }

            $updateFields[] = "username = :username";
            $params[':username'] = $username;
        }

        if (!empty($email)) {
            $updateFields[] = "email = :email";
            $params[':email'] = $email;
        }

        if (!empty($full_name)) {
            $updateFields[] = "full_name = :full_name";
            $params[':full_name'] = $full_name;
        }

        if (!empty($doctor_id_code)) {
            $updateFields[] = "doctor_id_code = :doctor_id_code";
            $params[':doctor_id_code'] = $doctor_id_code;
        }

        if (!empty($hospital_name)) {
            $updateFields[] = "hospital_name = :hospital_name";
            $params[':hospital_name'] = $hospital_name;
        }

        if (!empty($specialist)) {
            $updateFields[] = "specialist = :specialist";
            $params[':specialist'] = $specialist;
        }

        if (!empty($experience_years)) {
            $updateFields[] = "experience_years = :experience_years";
            $params[':experience_years'] = $experience_years;
        }

        if (empty($updateFields)) {
            echo json_encode(["status" => "error", "message" => "No fields to update"]);
            exit;
        }

        $query = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = :doctor_id";
        $stmt = $conn->prepare($query);

        if ($stmt->execute($params)) {
            echo json_encode([
                "status" => "success",
                "message" => "Doctor details updated successfully"
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update doctor details"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
