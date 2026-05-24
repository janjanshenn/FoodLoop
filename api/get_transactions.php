<?php
require_once 'db.php';
require_role(['admin', 'staff']);

// Optional parameters: filter by date or month
$date  = $_GET['date']  ?? null;
$month = $_GET['month'] ?? null;

if ($date) {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE DATE(created_at) = ? ORDER BY created_at DESC");
    $stmt->execute([$date]);
} elseif ($month) {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE DATE_FORMAT(created_at, '%Y-%m') = ? ORDER BY created_at DESC");
    $stmt->execute([$month]);
} else {
    $stmt = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 50");
}

$transactions = $stmt->fetchAll();
echo json_encode($transactions);
?>
