<?php
header("Content-Type: application/json");
require_once 'config.php';

// !!! PASTE KEY HERE !!!
$FIREBASE_SERVER_KEY = 'AIzaSyDgm4dWvSyGZX6UspQzHYVr3hF4G2pZjz8'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $systolic = $_POST['systolic'] ?? 0;
    $diastolic = $_POST['diastolic'] ?? 0;
    $sugar = $_POST['blood_sugar'] ?? 0;
    $temp = $_POST['body_temp'] ?? 0;
    $rate = $_POST['heart_rate'] ?? 0;
    $symptoms = $_POST['symptoms'] ?? '';
    
    // Determine Severity
    // Rule:
    // Normal: SBP < 140 AND DBP < 90
    // Gestational: (140 <= SBP < 160) OR (90 <= DBP < 110)
    // Severe High: SBP >= 160 OR DBP >= 110
    // Chronic: (This is a fallback or specific history, but for single reading we use these buckets)
    
    // We will use the python logic order:
    $severity = "Chronic Hypertension"; // Default fallback
    
    if ($systolic < 140 && $diastolic < 90) {
        $severity = "Normal";
    } elseif (($systolic >= 140 && $systolic < 160) || ($diastolic >= 90 && $diastolic < 110)) {
        $severity = "Gestational Hypertension";
    } elseif ($systolic >= 160 || $diastolic >= 110) {
        $severity = "Severe Hypertension (Eclampsia Risk)";
    }
    
    // Override if previously known as Chronic? No, we classify current reading.

    try {
        // 1. Insert Record
        $stmt = $conn->prepare("INSERT INTO hypertension_records (username, systolic, diastolic, blood_sugar, body_temp, heart_rate, severity, symptoms) VALUES (:u, :s, :d, :bs, :bt, :hr, :sev, :sym)");
        $stmt->bindParam(':u', $username);
        $stmt->bindParam(':s', $systolic);
        $stmt->bindParam(':d', $diastolic);
        $stmt->bindParam(':bs', $sugar);
        $stmt->bindParam(':bt', $temp);
        $stmt->bindParam(':hr', $rate);
        $stmt->bindParam(':sev', $severity);
        $stmt->bindParam(':sym', $symptoms);
        
        if ($stmt->execute()) {
            
            // 2. If Severe, Alert Doctor
            if ($severity == "Severe Hypertension (Eclampsia Risk)") {
                // Find user details to get added_by (Doctor)
                $userStmt = $conn->prepare("SELECT full_name, patient_id_code, added_by FROM users WHERE username = :u");
                $userStmt->bindParam(':u', $username);
                $userStmt->execute();
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userData && !empty($userData['added_by'])) {
                    $docUsername = $userData['added_by'];
                    $patientName = $userData['full_name'];
                    $patientId = $userData['patient_id_code'];
                    
                    // Add Alert to DB
                    $alertStmt = $conn->prepare("INSERT INTO doctor_alerts (doctor_username, patient_username, patient_name, patient_id, alert_type, alert_message, severity) VALUES (:doc, :pat, :pname, :pid, 'Hypertension', 'High BP Detected ($systolic/$diastolic)', 'High')");
                    $alertStmt->bindParam(':doc', $docUsername);
                    $alertStmt->bindParam(':pat', $username);
                    $alertStmt->bindParam(':pname', $patientName);
                    $alertStmt->bindParam(':pid', $patientId);
                    $alertStmt->execute();
                    
                    // Send FCM to Doctor
                    // Fetch Doctor Token
                    $docTokenStmt = $conn->prepare("SELECT fcm_token FROM users WHERE username = :doc");
                    $docTokenStmt->bindParam(':doc', $docUsername);
                    $docTokenStmt->execute();
                    $docUser = $docTokenStmt->fetch(PDO::FETCH_ASSOC);
                    $docToken = $docUser['fcm_token'] ?? '';
                    
                    if (!empty($docToken) && $FIREBASE_SERVER_KEY != 'YOUR_SERVER_KEY_HERE') {
                        sendFCMNotification($docToken, "Critical BP Alert", "Patient $patientName recorded Severe Hypertension!", $FIREBASE_SERVER_KEY);
                    }
                }
            }
            
            echo json_encode(["status" => "success", "message" => "Record saved", "severity" => $severity]);
        } else {
             echo json_encode(["status" => "error", "message" => "Failed to save record"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request"]);
}

function sendFCMNotification($token, $title, $body, $serverKey) {
    $url = "https://fcm.googleapis.com/fcm/send";
    $fields = [
        'to' => $token,
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
