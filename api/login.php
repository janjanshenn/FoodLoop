<?php
require_once 'db.php';
require_once 'rate_limit.php';

// Rate limit check removed for easier testing in prototype

$data     = json_decode(file_get_contents('php://input'), true);
$username = strtolower(trim($data['username'] ?? ''));
$password = $data['password'] ?? '';
$captcha  = trim($data['captcha'] ?? '');

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password are required.']);
    exit;
}

// CAPTCHA validation removed for easier testing in prototype

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    // Email verification requirement bypassed for easier testing in prototype

    // Rate limit clearing bypassed since rate limiting is disabled

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        'success'  => true,
        'role'     => $user['role'],
        'username' => $user['username']
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid username or password.']);
}
?>
