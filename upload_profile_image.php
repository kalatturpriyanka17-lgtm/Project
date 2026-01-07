<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logging for debugging
    $logFile = 'debug_upload.txt';
    $logData = date("Y-m-d H:i:s") . " - Upload Request\n";
    $logData .= "POST: " . print_r($_POST, true) . "\n";
    $logData .= "FILES: " . print_r($_FILES, true) . "\n";
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    
    // In some Retrofit/Multipart cases, strings might come with quotes
    $username = trim($username, '"'); 
    
    $logData .= "Cleaned Username: [$username]\n";

    if (empty($username)) {
        $logData .= "Error: Empty username\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        echo json_encode(["status" => "error", "message" => "Username is required"]);
        exit;
    }

    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
        $logData .= "Error: File upload fail\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        echo json_encode(["status" => "error", "message" => "No file uploaded or upload error"]);
        exit;
    }

    $uploadDir = 'uploads/profile_pics/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileName = $_FILES['profile_image']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Sanitized file name
    $newFileName = md5(time() . $username) . '.' . $fileExtension;
    $dest_path = $uploadDir . $newFileName;

    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
    if (in_array($fileExtension, $allowedfileExtensions)) {
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            try {
                // Update user profile with image path
                $query = "UPDATE users SET profile_image = :profile_image WHERE username = :username";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':profile_image', $newFileName);
                $stmt->bindParam(':username', $username);
                
                $stmt->execute();
                $rows = $stmt->rowCount();
                $logData .= "DB Update executed. Rows affected: $rows\n";

                if ($rows > 0) {
                    $logData .= "Success: DB updated\n";
                    echo json_encode([
                        "status" => "success",
                        "message" => "Profile picture uploaded successfully",
                        "image_url" => $newFileName
                    ]);
                } else {
                    // Check if user exists but has same image (unlikely with MD5 time) or if user not found at all
                    $check = $conn->prepare("SELECT id FROM users WHERE username = :u");
                    $check->execute([':u' => $username]);
                    if ($check->rowCount() == 0) {
                        $logData .= "Error: User $username not found in DB\n";
                        echo json_encode(["status" => "error", "message" => "User not found in database"]);
                    } else {
                        $logData .= "Success (no-change): DB update returned 0 but user exists\n";
                        echo json_encode(["status" => "success", "message" => "Profile pic saved (identically)"]);
                    }
                }
            } catch (PDOException $e) {
                $logData .= "DB Exception: " . $e->getMessage() . "\n";
                echo json_encode(["status" => "error", "message" => "Database error"]);
            }
        } else {
            $logData .= "Error: move_uploaded_file failed\n";
            echo json_encode(["status" => "error", "message" => "Error moving file"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid file extension"]);
    }
    file_put_contents($logFile, $logData, FILE_APPEND);
}
 else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
