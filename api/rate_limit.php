<?php
// Helper to implement IP-based rate limiting
function check_rate_limit($pdo, $ip, $action, $max_attempts = 5, $lockout_time = 15) {
    // Clear old attempts based on lockout_time (in minutes)
    $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE last_attempt < DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $stmt->execute([$lockout_time]);

    // Check current attempts
    $stmt = $pdo->prepare("SELECT attempts FROM rate_limits WHERE ip_address = ? AND action = ?");
    $stmt->execute([$ip, $action]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['attempts'] >= $max_attempts) {
        return false; // Rate limit exceeded
    }

    // Increment attempts
    $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, action, attempts) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()");
    $stmt->execute([$ip, $action]);

    return true; // Allowed
}

function clear_rate_limit($pdo, $ip, $action) {
    $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE ip_address = ? AND action = ?");
    $stmt->execute([$ip, $action]);
}
?>
