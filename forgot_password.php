<?php
header("Content-Type: application/json");
require_once 'config.php';

// Import PHPMailer classes (Ensure you have the folder in your project)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (!$data) { $data = $_POST; }

    $email = isset($data['email']) ? trim($data['email']) : '';

    if (empty($email)) {
        echo json_encode(["status" => "error", "message" => "Email required"]);
        exit;
    }

    try {
        // 1. Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            $resetLink = "http://10.113.57.3/php_backend/reset_password.php?email=" . urlencode($email);
            
            // 2. Setup PHPMailer (Direct Connection)
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'kalatturpriyanka17@gmail.com'; 
            $mail->Password   = 'wtsr uvzv ccxr wuds';   
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom('kalatturpriyanka17@gmail.com', 'PregSafe Team');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - PregSafe';
            $mail->Body    = "<h2>Hello,</h2><p>Click below to reset your password:</p><p><a href='$resetLink' style='background:#051024; color:#fff; padding:10px; text-decoration:none;'>Reset Password</a></p>";

            $mail->send();
            echo json_encode(["status" => "success", "message" => "Reset link sent to your email!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Email not found"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Mail Error: " . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
}
