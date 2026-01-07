<?php
header("Content-Type: application/json");
require_once 'config.php';

// !!! PASTE YOUR KEY HERE !!!
// Actual FCM Legacy Server Key
$FIREBASE_SERVER_KEY = 'AIzaSyDgm4dWvSyGZX6UspQzHYVr3hF4G2pZjz8'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_username = $_POST['doctor_username'] ?? '';
    // Patient username is better, but app sends Name/ID. 
    $patient_username = $_POST['patient_username'] ?? '';
    $medicines = $_POST['medicines'] ?? ''; // JSON string of medicines

    // Debug
    file_put_contents('debug_rx.txt', date("Y-m-d H:i:s") . " - RX Request for $patient_username\n", FILE_APPEND);

    if (empty($doctor_username) || empty($patient_username) || empty($medicines)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    try {
        // 1. Save Prescription to DB
        $stmt = $conn->prepare("INSERT INTO prescriptions (doctor_username, patient_username, medicines) VALUES (:doc, :pat, :meds)");
        $stmt->bindParam(':doc', $doctor_username);
        $stmt->bindParam(':pat', $patient_username);
        $stmt->bindParam(':meds', $medicines);
        
        if ($stmt->execute()) {
            
            // 2. Add Notification to patient_alerts Table
            $alertMsg = "Dr. $doctor_username has shared a new prescription for you.";
            $alertStmt = $conn->prepare("INSERT INTO patient_alerts (patient_username, doctor_username, alert_type, message) VALUES (:pat, :doc, 'prescription', :msg)");
            $alertStmt->bindParam(':pat', $patient_username);
            $alertStmt->bindParam(':doc', $doctor_username);
            $alertStmt->bindParam(':msg', $alertMsg);
            $alertStmt->execute();

            // 3. Send FCM to Patient
            $tokenStmt = $conn->prepare("SELECT fcm_token FROM users WHERE username = :pat");
            $tokenStmt->bindParam(':pat', $patient_username);
            $tokenStmt->execute();
            $user = $tokenStmt->fetch(PDO::FETCH_ASSOC);
            $token = $user['fcm_token'] ?? '';
            
            $fcmResult = "Not attempted";
            if (!empty($token)) {
                $fcmResult = sendFCMNotification($token, "New Prescription Received", $alertMsg, $FIREBASE_SERVER_KEY);
            } else {
                $fcmResult = "Token is Empty for user $patient_username";
            }

            echo json_encode([
                "status" => "success", 
                "message" => "Prescription shared successfully!",
                "fcm_status" => $fcmResult
            ]);

        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save prescription"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Db Error: " . $e->getMessage()]);
    }
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
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
?>
