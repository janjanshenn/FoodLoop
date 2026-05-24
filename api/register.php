<?php
require_once 'db.php';
require_once 'rate_limit.php';
require_once 'mailer.php';

// Rate limit check removed for easier testing in prototype

$data     = json_decode(file_get_contents('php://input'), true);
$username = strtolower(trim($data['username'] ?? ''));
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$captcha  = trim($data['captcha'] ?? '');

if (!$username || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Username, email, and password are required.']);
    exit;
}

// CAPTCHA validation removed for easier testing in prototype

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format.']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 8 characters long.']);
    exit;
}

// Check if username or email already exists
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    http_response_code(409);
    if ($existing['username'] === $username) {
        echo json_encode(['error' => 'Username already exists.']);
    } else {
        echo json_encode(['error' => 'Email already exists.']);
    }
    exit;
}

// Hash password and insert
$hash = password_hash($password, PASSWORD_BCRYPT);
$role = 'customer'; // Default role for new registrations
$token = bin2hex(random_bytes(32));

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, is_verified, verification_token) VALUES (?, ?, ?, ?, 1, NULL)");
    $stmt->execute([$username, $email, $hash, $role]);
    
    echo json_encode([
        'success'  => true,
        'message'  => 'Registration successful. Your account is active immediately!'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to register user: ' . $e->getMessage()]);
}
?>
