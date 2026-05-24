<?php
require_once 'db.php';
require_once 'rate_limit.php';
require_once 'mailer.php';

$ip = get_client_ip();
if (!check_rate_limit($pdo, $ip, 'forgot_password', 3, 15)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please try again later.']);
    exit;
}

$data  = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid email is required.']);
    exit;
}

// Check if user exists
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
    $stmt->execute([$otp, $expires, $user['id']]);

    send_email($email, "Password Reset OTP", "Hi " . $user['username'] . ",\n\nYour password reset OTP is: $otp\nThis OTP is valid for 15 minutes.");
}

// Always return success to prevent email enumeration
echo json_encode(['success' => true, 'message' => 'If that email exists, an OTP has been sent.']);
?>
