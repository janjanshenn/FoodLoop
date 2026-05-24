<?php
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Please log in first.']);
    exit;
}

$username = $_SESSION['username'];
$data     = json_decode(file_get_contents('php://input'), true);

$items = $data['items'] ?? [];
if (empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'No items in cart to checkout.']);
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($items as $item) {
        $menuId = intval($item['menu_id'] ?? 0);
        $qty    = intval($item['qty'] ?? 1);
        $price  = floatval($item['price'] ?? 0);
        $name   = trim($item['name'] ?? '');

        if ($qty <= 0 || !$name || $price <= 0) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Invalid item data in cart.']);
            exit;
        }

        // 1. Fetch current servings stock of the dish and lock it
        $dish = null;
        if ($menuId > 0) {
            $stmt = $pdo->prepare("SELECT id, name, servings FROM menu WHERE id = ? FOR UPDATE");
            $stmt->execute([$menuId]);
            $dish = $stmt->fetch();
        }

        if (!$dish) {
            // Fallback to name matching
            $stmtName = $pdo->prepare("SELECT id, name, servings FROM menu WHERE name = ? FOR UPDATE");
            $stmtName->execute([$name]);
            $dish = $stmtName->fetch();
        }

        if (!$dish) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['error' => "Menu item '{$name}' not found."]);
            exit;
        }

        if ($dish['servings'] < $qty) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => "Dish '{$dish['name']}' has insufficient servings left."]);
            exit;
        }

        // 2. Decrement servings stock
        $updateStmt = $pdo->prepare("UPDATE menu SET servings = servings - ? WHERE id = ?");
        $updateStmt->execute([$qty, $dish['id']]);

        // 3. Create the cart order reservation entry
        $insertStmt = $pdo->prepare(
            "INSERT INTO reservations (username, menu_id, item_name, price, quantity, order_type, status, created_at) 
             VALUES (?, ?, ?, ?, ?, 'Cart Order', 'Pending', NOW())"
        );
        $insertStmt->execute([$username, $dish['id'], $dish['name'], $price, $qty]);
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Cart checked out successfully and queued for preparation.'
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
