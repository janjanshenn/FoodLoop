<?php
require_once 'db.php';

// Verify session role
$role = $_SESSION['role'] ?? '';
if ($role !== 'admin' && $role !== 'staff') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden. Unauthorized role.']);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

$menu_id         = intval($data['menu_id'] ?? 0);
$servings_cooked = intval($data['servings_cooked'] ?? 0);
$ingredients     = $data['ingredients'] ?? [];

if ($menu_id <= 0 || $servings_cooked <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters: Menu ID and servings must be greater than zero.']);
    exit;
}

try {
    // Ensure the cooking_log table exists (run this outside transaction to avoid implicit commit)
    $pdo->exec("CREATE TABLE IF NOT EXISTS cooking_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_id INT NOT NULL,
        dish_name VARCHAR(100) NOT NULL,
        servings_cooked INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
    )");

    $pdo->beginTransaction();

    // 1. Fetch menu item details and lock the row
    $stmtMenu = $pdo->prepare("SELECT name, category FROM menu WHERE id = ? FOR UPDATE");
    $stmtMenu->execute([$menu_id]);
    $menuItem = $stmtMenu->fetch();

    if (!$menuItem) {
        throw new Exception("Selected dish not found in the menu.");
    }
    
    // Check if category is Main Dish
    if (trim(strtolower($menuItem['category'])) !== 'main dish') {
        throw new Exception("Only Main Dishes can be cooked at the Cooking Station.");
    }
    
    $dish_name = $menuItem['name'];

    // 2. Process and deduct ingredients
    foreach ($ingredients as $ing) {
        $ing_id = intval($ing['id'] ?? 0);
        $deduction_qty = floatval($ing['deduction_qty'] ?? 0);

        // If the deduction quantity is zero or less, skip it (meaning it wasn't used)
        if ($ing_id <= 0 || $deduction_qty <= 0) {
            continue;
        }

        // Fetch current stock and lock the row
        $stmtIng = $pdo->prepare("SELECT name, quantity, unit FROM ingredients WHERE id = ? FOR UPDATE");
        $stmtIng->execute([$ing_id]);
        $ingredient = $stmtIng->fetch();

        if (!$ingredient) {
            throw new Exception("Ingredient ID {$ing_id} not found in inventory.");
        }

        // Double check stock sufficiency
        if ($ingredient['quantity'] < $deduction_qty) {
            throw new Exception("Insufficient stock for {$ingredient['name']}. Available: {$ingredient['quantity']} {$ingredient['unit']}, Needed: {$deduction_qty} {$ingredient['unit']}.");
        }

        // Deduct from stock
        $stmtDeduct = $pdo->prepare("UPDATE ingredients SET quantity = quantity - ? WHERE id = ?");
        $stmtDeduct->execute([$deduction_qty, $ing_id]);
    }

    // 3. Increment servings for the cooked dish
    $stmtAddServings = $pdo->prepare("UPDATE menu SET servings = servings + ? WHERE id = ?");
    $stmtAddServings->execute([$servings_cooked, $menu_id]);

    // 4. Log the action to cooking_log
    $stmtLog = $pdo->prepare("INSERT INTO cooking_log (menu_id, dish_name, servings_cooked, created_at) VALUES (?, ?, ?, NOW())");
    $stmtLog->execute([$menu_id, $dish_name, $servings_cooked]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
