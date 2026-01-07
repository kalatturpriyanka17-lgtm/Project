<?php
header("Content-Type: application/json");
require_once 'config.php';

// ==========================================
// CONFIGURATION: PASTE YOUR FIREBASE SERVER KEY HERE
// ==========================================
$FIREBASE_SERVER_KEY = 'AIzaSyDgm4dWvSyGZX6UspQzHYVr3hF4G2pZjz8'; 
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_username = $_POST['patient_username'] ?? '';
    $report_type_param = $_POST['report_type'] ?? 'anaemia';
    $hb_level = $_POST['hb_level'] ?? 0;
    $severity = $_POST['severity'] ?? '';
    $systolic = $_POST['systolic'] ?? 0;
    $diastolic = $_POST['diastolic'] ?? 0;
    $fetal_weight = $_POST['fetal_weight'] ?? 0;

    // Debug Header
    file_put_contents('debug_share.txt', date("Y-m-d H:i:s") . " - Request received for $report_type_param\n", FILE_APPEND);

    if (empty($patient_username) || empty($severity)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    try {
        // 1. Get Patient Details and their Doctor (added_by)
        $stmt = $conn->prepare("SELECT full_name, patient_id_code, added_by FROM users WHERE username = :username");
        $stmt->bindParam(':username', $patient_username);
        $stmt->execute();
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            echo json_encode(["status" => "error", "message" => "Patient not found"]);
            exit;
        }

        $doctor_username = $patient['added_by'];
        $patient_name = $patient['full_name'];
        $patient_id = $patient['patient_id_code'];

        if (empty($doctor_username)) {
            $doctor_username = 'admin';  // Fallback
        }

        // 2. Insert Alert into Database
        $alert_type = ($report_type_param === 'hypertension') ? "Hypertension Report" : (($report_type_param === 'fetal_growth') ? "Fetal Growth Report" : "Anaemia Report");
        $pregnancy_week = $_POST['pregnancy_week'] ?? '';
        $red = $_POST['red_pixel'] ?? 0;
        $green = $_POST['green_pixel'] ?? 0;
        $blue = $_POST['blue_pixel'] ?? 0;
        
        if ($report_type_param === 'hypertension') {
            $message = "Patient $patient_name ($patient_id) has shared a Hypertension Report. BP: $systolic/$diastolic, Severity: $severity";
        } elseif ($report_type_param === 'fetal_growth') {
            $message = "Patient $patient_name ($patient_id) has shared a Fetal Growth Report. Weight: {$fetal_weight}g, Status: $severity";
        } else {
            $message = "Patient $patient_name ($patient_id) has shared an Anaemia Report. Severity: $severity";
        }

        $insertStmt = $conn->prepare("INSERT INTO doctor_alerts (doctor_username, patient_username, patient_name, patient_id, alert_type, alert_message, severity, pregnancy_week, red_pixel, green_pixel, blue_pixel, hb_level, systolic, diastolic, fetal_weight) VALUES (:doctor, :patient, :name, :pid, :type, :msg, :sev, :week, :red, :green, :blue, :hb, :sys, :dia, :fetal)");
        $insertStmt->bindParam(':doctor', $doctor_username);
        $insertStmt->bindParam(':patient', $patient_username);
        $insertStmt->bindParam(':name', $patient_name);
        $insertStmt->bindParam(':pid', $patient_id);
        $insertStmt->bindParam(':type', $alert_type);
        $insertStmt->bindParam(':msg', $message);
        $insertStmt->bindParam(':sev', $severity);
        $insertStmt->bindParam(':week', $pregnancy_week);
        $insertStmt->bindParam(':red', $red);
        $insertStmt->bindParam(':green', $green);
        $insertStmt->bindParam(':blue', $blue);
        $insertStmt->bindParam(':hb', $hb_level);
        $insertStmt->bindParam(':sys', $systolic);
        $insertStmt->bindParam(':dia', $diastolic);
        $insertStmt->bindParam(':fetal', $fetal_weight);

        $insertReview = $insertStmt->execute();

        // 3. SEND INSTANT NOTIFICATION (FCM)
        if ($insertReview) {
            // Fetch Doctor's FCM Token
            $tokenStmt = $conn->prepare("SELECT fcm_token FROM users WHERE username = :doctor");
            $tokenStmt->bindParam(':doctor', $doctor_username);
            $tokenStmt->execute();
            $doctorData = $tokenStmt->fetch(PDO::FETCH_ASSOC);
            $doctorToken = $doctorData['fcm_token'] ?? '';

            if (!empty($doctorToken) && $FIREBASE_SERVER_KEY != 'YOUR_SERVER_KEY_HERE') {
                sendFCMNotification($doctorToken, "Medical Report Shared", $message, $FIREBASE_SERVER_KEY);
            }

            echo json_encode(["status" => "success", "message" => "Report shared successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to share report"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
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
    file_put_contents('debug_fcm.txt', "FCM Result: " . $result . "\n", FILE_APPEND);
    curl_close($ch);
}
?>
