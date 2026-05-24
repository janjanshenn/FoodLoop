<?php
require_once 'db.php';
require_role(['admin', 'staff']);

$stmt = $pdo->query("SELECT * FROM ingredients ORDER BY name");
$stock = $stmt->fetchAll();

// Mark each item as Low Stock if quantity <= threshold
foreach ($stock as &$item) {
    $item['status'] = ($item['quantity'] <= $item['low_threshold']) ? 'Low Stock' : 'In Stock';
}

echo json_encode($stock);
?>
