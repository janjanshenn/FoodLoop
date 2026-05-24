<?php
require_once 'db.php';
require_role(['admin', 'staff']);

$data = json_decode(file_get_contents('php://input'), true);

$id       = intval($data['id'] ?? 0);
$quantity = floatval($data['quantity'] ?? 0);
$unit     = trim($data['unit'] ?? '');
$name     = trim($data['name'] ?? '');

// Insert new ingredient
if ($id === 0 && $name) {
    $low_threshold = floatval($data['low_threshold'] ?? 5);
    $stmt = $pdo->prepare("INSERT INTO ingredients (name, quantity, unit, low_threshold) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $quantity, $unit, $low_threshold]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// Update existing ingredient
if ($id > 0) {
    if ($name) {
        $low_threshold = floatval($data['low_threshold'] ?? 5);
        $stmt = $pdo->prepare("UPDATE ingredients SET name = ?, quantity = ?, unit = ?, low_threshold = ? WHERE id = ?");
        $stmt->execute([$name, $quantity, $unit, $low_threshold, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE ingredients SET quantity = ?, unit = ? WHERE id = ?");
        $stmt->execute([$quantity, $unit, $id]);
    }
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid input.']);
?>
