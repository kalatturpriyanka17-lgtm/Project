<?php
header("Content-Type: application/json");
require_once 'config.php';

$FIREBASE_SERVER_KEY = 'AIzaSyDgm4dWvSyGZX6UspQzHYVr3hF4G2pZjz8';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_username = $_POST['doctor_username'] ?? '';
    $patient_username = $_POST['patient_username'] ?? '';
    $reply_message = $_POST['message'] ?? '';
    // Optional: Only used if we want to thread conversations
    $original_query = $_POST['original_query'] ?? ''; 

    // Debug logging
    $logData = date("Y-m-d H:i:s") . " - Reply Request: Doc=$doctor_username, Pat=$patient_username, Msg=$reply_message\n";
    file_put_contents('debug_reply_log.txt', $logData, FILE_APPEND);

    if (empty($doctor_username) || empty($patient_username) || empty($reply_message)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    try {
        // 1. Fetch Patient Details (Name, ID) for the alert
        $stmt = $conn->prepare("SELECT full_name, patient_id_code FROM users WHERE username = :pat");
        $stmt->bindParam(':pat', $patient_username);
        $stmt->execute();
        $patientData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $patientName = $patientData['full_name'] ?? 'Unknown';
        $patientId = $patientData['patient_id_code'] ?? 'Unknown';

        // 2. Insert into doctor_alerts
        // We use alert_type 'query_reply' to distinguish
        $alertStmt = $conn->prepare("INSERT INTO doctor_alerts 
            (doctor_username, patient_username, patient_name, patient_id, alert_type, alert_message, is_read) 
            VALUES (:doc, :pat, :pName, :pId, 'query_reply', :msg, 0)");
            
        $alertStmt->bindParam(':doc', $doctor_username);
        $alertStmt->bindParam(':pat', $patient_username);
        $alertStmt->bindParam(':pName', $patientName);
        $alertStmt->bindParam(':pId', $patientId);
        $alertStmt->bindParam(':msg', $reply_message);
        
        if ($alertStmt->execute()) {
            
            // 3. Send Notification to Doctor
            $tokenStmt = $conn->prepare("SELECT fcm_token FROM users WHERE username = :doc");
            $tokenStmt->bindParam(':doc', $doctor_username);
            $tokenStmt->execute();
            $docData = $tokenStmt->fetch(PDO::FETCH_ASSOC);
            $docToken = $docData['fcm_token'] ?? '';

            if (!empty($docToken)) {
                $title = "Query Reply: $patientName";
                sendFCMNotification($docToken, $title, "Patient replied: $reply_message", $FIREBASE_SERVER_KEY);
            }

            echo json_encode(["status" => "success", "message" => "Reply sent successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save reply"]);
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
