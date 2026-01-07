<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        $data = $_POST;
    }

    $patient_id = isset($data['patient_id']) ? trim($data['patient_id']) : '';
    $username = isset($data['username']) ? trim($data['username']) : '';
    $password = isset($data['password']) ? trim($data['password']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $full_name = isset($data['full_name']) ? trim($data['full_name']) : '';
    $patient_id_code = isset($data['patient_id_code']) ? trim($data['patient_id_code']) : '';
    $pregnancy_week = isset($data['pregnancy_week']) ? trim($data['pregnancy_week']) : '';
    $mobile_number = isset($data['mobile_number']) ? trim($data['mobile_number']) : '';
    $health_issues = isset($data['health_issues']) ? trim($data['health_issues']) : '';
    $caretaker_name = isset($data['caretaker_name']) ? trim($data['caretaker_name']) : '';
    $caretaker_relation = isset($data['caretaker_relation']) ? trim($data['caretaker_relation']) : '';
    $caretaker_mobile = isset($data['caretaker_mobile']) ? trim($data['caretaker_mobile']) : '';

    if (empty($patient_id)) {
        echo json_encode(["status" => "error", "message" => "Patient ID is required"]);
        exit;
    }

    try {
        $updateFields = [];
        $params = [':patient_id' => $patient_id];

        if (!empty($username)) {
            // Check if username already exists for someone else
            $check_query = "SELECT id FROM users WHERE username = :username AND id != :patient_id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':username', $username);
            $check_stmt->bindParam(':patient_id', $patient_id);
            $check_stmt->execute();
            if ($check_stmt->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "Username already exists"]);
                exit;
            }
            $updateFields[] = "username = :username";
            $params[':username'] = $username;
        }

        if (!empty($password)) {
            $updateFields[] = "password = :password";
            $params[':password'] = $password;
        }

        if (!empty($email)) {
            $updateFields[] = "email = :email";
            $params[':email'] = $email;
        }

        if (!empty($full_name)) {
            $updateFields[] = "full_name = :full_name";
            $params[':full_name'] = $full_name;
        }

        if (!empty($patient_id_code)) {
            $updateFields[] = "patient_id_code = :patient_id_code";
            $params[':patient_id_code'] = $patient_id_code;
        }

        if (!empty($pregnancy_week)) {
            $updateFields[] = "pregnancy_week = :pregnancy_week";
            $params[':pregnancy_week'] = $pregnancy_week;
        }

        if (!empty($mobile_number)) {
            $updateFields[] = "mobile_number = :mobile_number";
            $params[':mobile_number'] = $mobile_number;
        }

        if (!empty($health_issues)) {
            $updateFields[] = "health_issues = :health_issues";
            $params[':health_issues'] = $health_issues;
        }

        if (!empty($caretaker_name)) {
            $updateFields[] = "caretaker_name = :caretaker_name";
            $params[':caretaker_name'] = $caretaker_name;
        }

        if (!empty($caretaker_relation)) {
            $updateFields[] = "caretaker_relationship = :caretaker_relationship"; // Note: DB column is caretaker_relationship
            $params[':caretaker_relationship'] = $caretaker_relation;
        }

        if (!empty($caretaker_mobile)) {
            $updateFields[] = "caretaker_mobile = :caretaker_mobile";
            $params[':caretaker_mobile'] = $caretaker_mobile;
        }

        if (empty($updateFields)) {
            echo json_encode(["status" => "error", "message" => "No fields to update"]);
            exit;
        }

        $query = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = :patient_id AND role = 'patient'";
        $stmt = $conn->prepare($query);

        if ($stmt->execute($params)) {
            $affected = $stmt->rowCount();
            echo json_encode(["status" => "success", "message" => "Patient updated successfully", "affected_rows" => $affected]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update patient"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
