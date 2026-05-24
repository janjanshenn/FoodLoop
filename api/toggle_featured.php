<?php
require_once 'db.php';

// Verify session role (restricted to admin and staff)
$role = $_SESSION['role'] ?? '';
if ($role !== 'admin' && $role !== 'staff') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden. Access restricted.']);
    exit;
}

// Decode input JSON
$data = json_decode(file_get_contents('php://input'), true);
$itemId = intval($data['id'] ?? 0);

if ($itemId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Menu Item ID.']);
    exit;
}

try {
    // 1. Ensure columns exist and query current state
    $checkColumn = $pdo->query("SHOW COLUMNS FROM menu LIKE 'is_featured'")->fetch();
    if (!$checkColumn) {
        $pdo->exec("ALTER TABLE menu ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
    }

    $stmt = $pdo->prepare("SELECT is_featured FROM menu WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();

    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Menu item not found.']);
        exit;
    }

    $currentStatus = intval($item['is_featured']);
    $newStatus = $currentStatus === 1 ? 0 : 1;

    // 2. Enforce the limit of 8 featured items
    if ($newStatus === 1) {
        $stmtCount = $pdo->query("SELECT COUNT(*) as count FROM menu WHERE is_featured = 1");
        $countRow = $stmtCount->fetch();
        $currentFeaturedCount = intval($countRow['count']);

        if ($currentFeaturedCount >= 8) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Limit reached. You can feature a maximum of 8 items. Please unfeature another item first.'
            ]);
            exit;
        }
    }

    // 3. Update status
    $stmtUpdate = $pdo->prepare("UPDATE menu SET is_featured = ? WHERE id = ?");
    $stmtUpdate->execute([$newStatus, $itemId]);

    echo json_encode([
        'success' => true,
        'is_featured' => $newStatus,
        'message' => $newStatus === 1 ? 'Item added to featured carousel!' : 'Item removed from featured carousel.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
