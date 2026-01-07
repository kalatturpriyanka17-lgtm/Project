<?php
header("Content-Type: application/json");
require_once 'config.php';

$FIREBASE_SERVER_KEY = 'AIzaSyDgm4dWvSyGZX6UspQzHYVr3hF4G2pZjz8';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_username = $_POST['doctor_username'] ?? '';
    $patient_username = $_POST['patient_username'] ?? '';
    $alert_type = $_POST['alert_type'] ?? ''; // 'query' or 'diet'
    $message = $_POST['message'] ?? '';

    if (empty($doctor_username) || empty($patient_username) || empty($alert_type) || empty($message)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    try {
        // 1. Insert into patient_alerts
        $stmt = $conn->prepare("INSERT INTO patient_alerts (patient_username, doctor_username, alert_type, message) VALUES (:patient, :doctor, :type, :msg)");
        $stmt->bindParam(':patient', $patient_username);
        $stmt->bindParam(':doctor', $doctor_username);
        $stmt->bindParam(':type', $alert_type);
        $stmt->bindParam(':msg', $message);
        
        if ($stmt->execute()) {
            // 2. Fetch Patient's FCM Token for Push Notification
            $tokenStmt = $conn->prepare("SELECT fcm_token FROM users WHERE username = :patient");
            $tokenStmt->bindParam(':patient', $patient_username);
            $tokenStmt->execute();
            $patientData = $tokenStmt->fetch(PDO::FETCH_ASSOC);
            $patientToken = $patientData['fcm_token'] ?? '';

            if (!empty($patientToken)) {
                $title = ($alert_type === 'query') ? "New Clinical Query" : "New Diet Recommendation";
                sendFCMNotification($patientToken, $title, $message, $FIREBASE_SERVER_KEY);
            }

            echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save alert"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

function sendFCMNotification($to, $title, $body, $serverKey) {
    $url = "https://fcm.googleapis.com/fcm/send";
    $fields = [
        'to' => $to,
        'priority' => 'high',
        'notification' => [
            'title' => $title,
            'body' => $body,
            'sound' => 'default'
        ]
    ];
    $headers = [
        'Authorization: key=' . $serverKey,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_exec($ch);
    curl_close($ch);
}
?>
