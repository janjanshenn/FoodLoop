<?php
require_once 'db.php';
require_role(['admin', 'staff']);

$data = json_decode(file_get_contents('php://input'), true);

$items   = $data['items'] ?? [];
$total   = floatval($data['total'] ?? 0);
$cashier = trim($data['cashier'] ?? 'Staff');

if (empty($items) || $total <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No items in order.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verify servings availability and deduct
    foreach ($items as $item) {
        $name = trim($item['name'] ?? '');
        $qty  = intval($item['qty'] ?? 1);

        $checkStmt = $pdo->prepare("SELECT id, name, servings FROM menu WHERE name = ? FOR UPDATE");
        $checkStmt->execute([$name]);
        $dish = $checkStmt->fetch();

        if ($dish) {
            if ($dish['servings'] < $qty) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['error' => "Dish '{$dish['name']}' has insufficient servings left."]);
                exit;
            }

            $updateStmt = $pdo->prepare("UPDATE menu SET servings = servings - ? WHERE id = ?");
            $updateStmt->execute([$qty, $dish['id']]);
        }
    }

    // 2. Build summary string and insert transaction record
    $summary = implode(', ', array_map(fn($i) => $i['qty'] . 'x ' . $i['name'], $items));
    $stmt = $pdo->prepare(
        "INSERT INTO transactions (cashier, items_summary, total, created_at) VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$cashier, $summary, $total]);
    $transactionId = $pdo->lastInsertId();

    $pdo->commit();
    echo json_encode(['success' => true, 'transaction_id' => $transactionId]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>

