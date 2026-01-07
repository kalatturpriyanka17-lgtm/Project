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
        $full_name = isset($data['full_name']) ? $data['full_name'] : '';
        $doctor_id_code = isset($data['doctor_id_code']) ? $data['doctor_id_code'] : '';
        $hospital_name = isset($data['hospital_name']) ? $data['hospital_name'] : '';
        $specialist = isset($data['specialist']) ? $data['specialist'] : '';
        $experience_years = isset($data['experience_years']) ? $data['experience_years'] : '';
    } else {
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $full_name = isset($_POST['full_name']) ? $_POST['full_name'] : '';
        $doctor_id_code = isset($_POST['doctor_id_code']) ? $_POST['doctor_id_code'] : '';
        $hospital_name = isset($_POST['hospital_name']) ? $_POST['hospital_name'] : '';
        $specialist = isset($_POST['specialist']) ? $_POST['specialist'] : '';
        $experience_years = isset($_POST['experience_years']) ? $_POST['experience_years'] : '';
    }

    if (empty($username) || empty($password) || empty($email) || empty($full_name) || empty($doctor_id_code)) {
        echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
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

        $query = "INSERT INTO users (username, password, email, role, full_name, doctor_id_code, hospital_name, specialist, experience_years) 
                  VALUES (:username, :password, :email, 'doctor', :full_name, :doctor_id_code, :hospital_name, :specialist, :experience_years)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':doctor_id_code', $doctor_id_code);
        $stmt->bindParam(':hospital_name', $hospital_name);
        $stmt->bindParam(':specialist', $specialist);
        $stmt->bindParam(':experience_years', $experience_years);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Doctor added successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add doctor"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
