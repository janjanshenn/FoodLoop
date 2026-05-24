<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Please log in first.']);
    exit;
}

$username = $_SESSION['username'];
$role     = $_SESSION['role'] ?? 'customer';

try {
    if ($role === 'admin' || $role === 'staff') {
        // Admins and staff see all reservations
        $stmt = $pdo->query("SELECT * FROM reservations ORDER BY created_at DESC");
        $reservations = $stmt->fetchAll();
    } else {
        // Customers only see their own reservations
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE username = ? ORDER BY created_at DESC");
        $stmt->execute([$username]);
        $reservations = $stmt->fetchAll();
    }

    echo json_encode($reservations);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
