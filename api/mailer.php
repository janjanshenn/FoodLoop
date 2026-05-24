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
            $value = trim(trim($value), '"\'');
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

function send_email($to, $subject, $message) {
    // Local development fallback: write emails to a local file
    $log_file = __DIR__ . '/../emails.log';
    $time = date('Y-m-d H:i:s');
    $log_entry = "========================================\n";
    $log_entry .= "[$time] To: $to\nSubject: $subject\n\n$message\n";
    $log_entry .= "========================================\n\n";
    
    // Create or append to log
    return file_put_contents($log_file, $log_entry, FILE_APPEND) !== false;
}
?>
