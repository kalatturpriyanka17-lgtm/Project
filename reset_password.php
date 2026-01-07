<?php
require_once 'config.php';

$message = "";
$status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $new_password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($username) || empty($new_password)) {
        $message = "Please fill all fields.";
        $status = "error";
    } else {
        try {
            // Verify username and email match
            $checkQuery = "SELECT id FROM users WHERE email = :email AND username = :username LIMIT 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                // Update password
                $updateQuery = "UPDATE users SET password = :password WHERE email = :email AND username = :username";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bindParam(':password', $new_password);
                $updateStmt->bindParam(':email', $email);
                $updateStmt->bindParam(':username', $username);
                
                if ($updateStmt->execute()) {
                    $message = "Password updated successfully! You can now login in the app.";
                    $status = "success";
                } else {
                    $message = "Failed to update password.";
                    $status = "error";
                }
            } else {
                $message = "Invalid username for this email address.";
                $status = "error";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $status = "error";
        }
    }
} else {
    $email = isset($_GET['email']) ? $_GET['email'] : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PregSafe</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { color: #051024; margin-bottom: 10px; text-align: center; }
        p { color: #666; text-align: center; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 14px; background-color: #051024; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s; }
        button:hover { background-color: #4FD3C4; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .alert-error { background-color: #fde8e8; color: #c81e1e; border: 1px solid #f8b4b4; }
        .alert-success { background-color: #defadb; color: #03543f; border: 1px solid #bcf0da; }
        .logo { width: 60px; display: block; margin: 0 auto 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Reset Password</h2>
        <p>Enter your username and new password to continue.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($status !== 'success'): ?>
        <form method="POST">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter your username">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" required placeholder="Enter new password">
            </div>
            <button type="submit">Update Password</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
