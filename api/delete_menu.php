<?php
require_once 'db.php';
require_role(['admin', 'staff']);

$data = json_decode(file_get_contents('php://input'), true);
$id   = intval($data['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID.']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM menu WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(['success' => true]);
?>
