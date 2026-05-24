<?php
require_once 'db.php';
require_once 'rate_limit.php';

$ip = get_client_ip();
if (!check_rate_limit($pdo, $ip, 'reset_password', 5, 15)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please try again later.']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$email    = trim($data['email'] ?? '');
$otp      = trim($data['otp'] ?? '');
$password = $data['new_password'] ?? '';

if (!$email || !$otp || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email, OTP, and new password are required.']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 8 characters long.']);
    exit;
}

// Find user with matching email and OTP
$stmt = $pdo->prepare("SELECT id, reset_token_expires FROM users WHERE email = ? AND reset_token = ?");
$stmt->execute([$email, $otp]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or expired OTP.']);
    exit;
}

// Check if OTP is expired (compares Unix timestamps, avoiding PHP vs MySQL timezone mismatch)
if (strtotime($user['reset_token_expires']) < time()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or expired OTP.']);
    exit;
}

// Update password and invalidate token
$hash = password_hash($password, PASSWORD_BCRYPT);
try {
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
    $stmt->execute([$hash, $user['id']]);

    clear_rate_limit($pdo, $ip, 'reset_password');

    echo json_encode(['success' => true, 'message' => 'Password reset successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to reset password.']);
}
?>
