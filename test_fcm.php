<?php
require_once 'config.php';

// !!! PASTE KEY HERE AGAIN IF NEEDED, OR IT WILL USE Config IF DEFINED THERE (IT IS NOT) !!!
$FIREBASE_SERVER_KEY = 'YOUR_SERVER_KEY_HERE'; 

$username = $_GET['username'] ?? '';

if (empty($username)) {
    die("<h3>Error: Please add ?username=YOUR_PATIENT_USERNAME to the URL.</h3>Example: test_fcm.php?username=sarah");
}

echo "<h2>FCM Debug Tool</h2>";
echo "Checking for user: <b>$username</b><br>";

// 1. Check Token
$stmt = $conn->prepare("SELECT fcm_token FROM users WHERE username = :u");
$stmt->bindParam(':u', $username);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("❌ User not found in database.");
}

$token = $user['fcm_token'];

if (empty($token)) {
    die("❌ User exists, but <b>fcm_token is NULL or EMPTY</b>.<br>Please Log In as this user in the App to sync the token.");
}

echo "✅ Token Found: " . substr($token, 0, 20) . "...<br>";

// 2. Check Key
if ($FIREBASE_SERVER_KEY == 'YOUR_SERVER_KEY_HERE') {
    die("❌ <b>Server Key not set!</b> Open this file (test_fcm.php) and paste your key.");
}
echo "✅ Server Key Set.<br>";

// 3. Send Test
echo "<hr>Sending Test Notification...<br>";

$url = "https://fcm.googleapis.com/fcm/send";
$fields = [
    'to' => $token,
    'priority' => 'high',
    'notification' => [
        'title' => "Test Notification",
        'body' => "This is a test from the Debug Tool. If you see this, FCM is working!",
        'sound' => 'default'
    ]
];
$headers = [
    'Authorization: key=' . $FIREBASE_SERVER_KEY,
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

echo "<h3>Result from Google:</h3>";
echo "<pre>" . htmlspecialchars($result) . "</pre>";

$json = json_decode($result, true);
if (isset($json['success']) && $json['success'] == 1) {
    echo "<h3 style='color:green'>SUCCESS! Notification Sent.</h3>";
} else {
    echo "<h3 style='color:red'>FAILED. See error above.</h3>";
}
?>
