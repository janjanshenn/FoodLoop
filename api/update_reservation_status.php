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

$data   = json_decode(file_get_contents('php://input'), true);
$resId  = intval($data['id'] ?? 0);
$action = trim($data['action'] ?? '');

if ($resId <= 0 || !$action) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Fetch current reservation details
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? FOR UPDATE");
    $stmt->execute([$resId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Reservation not found.']);
        exit;
    }

    $currentStatus = $reservation['status'];

    // 2. Authorization and state transition validation
    if ($action === 'cancel') {
        // Can only cancel if currently Pending or Confirmed
        if ($currentStatus !== 'Pending' && $currentStatus !== 'Confirmed') {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Cannot cancel a reservation that is ' . $currentStatus]);
            exit;
        }

        // Customers can only cancel their own reservations
        if ($role === 'customer' && $reservation['username'] !== $username) {
            $pdo->rollBack();
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized to cancel this reservation.']);
            exit;
        }

        // Action: Cancel
        // Refund servings back to the menu table
        $refunded = false;
        if (!empty($reservation['menu_id'])) {
            $updateMenu = $pdo->prepare("UPDATE menu SET servings = servings + ? WHERE id = ?");
            $updateMenu->execute([$reservation['quantity'], $reservation['menu_id']]);
            if ($updateMenu->rowCount() > 0) {
                $refunded = true;
            }
        }
        if (!$refunded) {
            $updateMenu = $pdo->prepare("UPDATE menu SET servings = servings + ? WHERE name = ?");
            $updateMenu->execute([$reservation['quantity'], $reservation['item_name']]);
        }

        // Update reservation status
        $updateRes = $pdo->prepare("UPDATE reservations SET status = 'Cancelled' WHERE id = ?");
        $updateRes->execute([$resId]);

    } elseif ($action === 'confirm') {
        // Staff/Admin only
        if ($role !== 'admin' && $role !== 'staff') {
            $pdo->rollBack();
            http_response_code(403);
            echo json_encode(['error' => 'Only admin and staff can confirm reservations.']);
            exit;
        }

        if ($currentStatus !== 'Pending') {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Only Pending reservations can be confirmed.']);
            exit;
        }

        // Update reservation status
        $updateRes = $pdo->prepare("UPDATE reservations SET status = 'Confirmed' WHERE id = ?");
        $updateRes->execute([$resId]);

    } elseif ($action === 'complete') {
        // Staff/Admin only
        if ($role !== 'admin' && $role !== 'staff') {
            $pdo->rollBack();
            http_response_code(403);
            echo json_encode(['error' => 'Only admin and staff can complete reservations.']);
            exit;
        }

        if ($currentStatus !== 'Confirmed') {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Only Confirmed reservations can be completed.']);
            exit;
        }

        // Update reservation status
        $updateRes = $pdo->prepare("UPDATE reservations SET status = 'Completed' WHERE id = ?");
        $updateRes->execute([$resId]);

        // Record a transaction for the sale
        $cashier = $username;
        $typeSuffix = ($reservation['order_type'] === 'Cart Order') ? ' (Order)' : ' (Reserved)';
        $summary = $reservation['quantity'] . 'x ' . $reservation['item_name'] . $typeSuffix;
        $total   = floatval($reservation['price']) * intval($reservation['quantity']);

        $transStmt = $pdo->prepare(
            "INSERT INTO transactions (cashier, items_summary, total, created_at) VALUES (?, ?, ?, NOW())"
        );
        $transStmt->execute([$cashier, $summary, $total]);

    } else {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action.']);
        exit;
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
