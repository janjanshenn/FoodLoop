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

$itemName = trim($data['item_name'] ?? '');
$price    = floatval($data['price']    ?? 0);
$quantity = intval($data['quantity']   ?? 1);

if (!$itemName || $price <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input parameters.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Fetch current servings stock of the dish
    $stmt = $pdo->prepare("SELECT id, servings FROM menu WHERE name = ? FOR UPDATE");
    $stmt->execute([$itemName]);
    $dish = $stmt->fetch();

    if (!$dish) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Menu item not found.']);
        exit;
    }

    if ($dish['servings'] < $quantity) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'Dish is out of stock / insufficient servings left.']);
        exit;
    }

    // 2. Decrement servings stock
    $updateStmt = $pdo->prepare("UPDATE menu SET servings = servings - ? WHERE id = ?");
    $updateStmt->execute([$quantity, $dish['id']]);

    // 3. Create the reservation
    $insertStmt = $pdo->prepare(
        "INSERT INTO reservations (username, menu_id, item_name, price, quantity, order_type, status, created_at) VALUES (?, ?, ?, ?, ?, 'Reservation', 'Pending', NOW())"
    );
    $insertStmt->execute([$username, $dish['id'], $itemName, $price, $quantity]);
    $reservationId = $pdo->lastInsertId();

    $pdo->commit();
    echo json_encode([
        'success'        => true,
        'reservation_id' => $reservationId,
        'message'        => 'Reservation successfully recorded.'
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
