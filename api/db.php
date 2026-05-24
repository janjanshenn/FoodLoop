<?php
// Simple environment variable loader for .env file (if exists in root)
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            // Remove quotes if present
            $value = trim($value, '"\'');
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

$host     = 'localhost';
$db_name  = 'foodloop_db';
$username = 'root';
$password = '';  // XAMPP default has no password
$port     = '3306';

$db_url = getenv('DATABASE_URL');
if ($db_url) {
    $dbparts = parse_url($db_url);
    $host = $dbparts['host'] ?? $host;
    $port = $dbparts['port'] ?? $port;
    $username = $dbparts['user'] ?? $username;
    $password = $dbparts['pass'] ?? $password;
    $db_name = isset($dbparts['path']) ? ltrim($dbparts['path'], '/') : $db_name;
} else {
    if (getenv('DB_HOST') !== false)     $host     = getenv('DB_HOST');
    if (getenv('DB_NAME') !== false)     $db_name  = getenv('DB_NAME');
    if (getenv('DB_USER') !== false)     $username = getenv('DB_USER');
    if (getenv('DB_PASS') !== false)     $password = getenv('DB_PASS');
    if (getenv('DB_PORT') !== false)     $port     = getenv('DB_PORT');
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Secure Session Initialization (supporting SSL offloading / reverse proxies)
if (session_status() === PHP_SESSION_NONE) {
    $is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
              || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => $is_secure,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Get secure client IP address supporting load balancers and reverse proxies
function get_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        $ip = $ips[0];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
}

// Helper to enforce session role requirements
function require_role($allowed_roles) {
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, $allowed_roles)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden. Unauthorized access.']);
        exit;
    }
}

// Allow requests from the browser (CORS for local dev)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Note: If using credentials across domains, this cannot be '*'
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Security Headers against XSS and Clickjacking
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
?>
