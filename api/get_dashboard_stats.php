<?php
require_once 'db.php';
require_role(['admin', 'staff']);

// 1. Total Sales Today
$stmt = $pdo->query("SELECT SUM(total) as total_sales FROM transactions WHERE DATE(created_at) = CURDATE()");
$sales_row = $stmt->fetch();
$sales_today = $sales_row['total_sales'] ? floatval($sales_row['total_sales']) : 0.0;

// 2. Completed Transactions Today (used as Active/Recent Orders metrics)
$stmt = $pdo->query("SELECT COUNT(*) as tx_count FROM transactions WHERE DATE(created_at) = CURDATE()");
$tx_row = $stmt->fetch();
$tx_count_today = intval($tx_row['tx_count']);

// 3. Low Stock Items (quantity <= low_threshold)
$stmt = $pdo->query("SELECT name, quantity, unit, updated_at FROM ingredients WHERE quantity <= low_threshold ORDER BY name");
$low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$low_stock_count = count($low_stock_items);

$low_stock_names = [];
foreach ($low_stock_items as $item) {
    $low_stock_names[] = $item['name'];
}
$low_stock_list_str = empty($low_stock_names) ? 'None' : implode(', ', $low_stock_names);

// 4. Weekly Sales Trend (last 7 days)
$weekly_labels = [];
$weekly_revenue = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('D', strtotime("-$i days"));
    $weekly_labels[] = $day_name;
    
    $stmt = $pdo->prepare("SELECT SUM(total) as daily_total FROM transactions WHERE DATE(created_at) = ?");
    $stmt->execute([$date]);
    $row = $stmt->fetch();
    $weekly_revenue[] = $row['daily_total'] ? floatval($row['daily_total']) : 0.0;
}

// 5. Top Selling Items (parse items_summary to compile item distribution)
$stmt = $pdo->query("SELECT items_summary FROM transactions LIMIT 200");
$summaries = $stmt->fetchAll(PDO::FETCH_COLUMN);

$item_counts = [];
foreach ($summaries as $summary) {
    // Format: "2x Classic Pork Adobo, 1x Sprite (Bottle)"
    $parts = explode(',', $summary);
    foreach ($parts as $part) {
        $part = trim($part);
        if (preg_match('/^(\d+)x\s+(.+)$/', $part, $matches)) {
            $qty = intval($matches[1]);
            $name = trim($matches[2]);
            // Clean up name variations if any
            if (!isset($item_counts[$name])) {
                $item_counts[$name] = 0;
            }
            $item_counts[$name] += $qty;
        }
    }
}
arsort($item_counts);
$top_items = array_slice($item_counts, 0, 4, true);
$total_top_qty = array_sum($top_items);

$top_items_data = [];
if ($total_top_qty > 0) {
    foreach ($top_items as $name => $qty) {
        $percentage = round(($qty / $total_top_qty) * 100);
        $top_items_data[] = [
            'name' => $name,
            'percentage' => $percentage
        ];
    }
} else {
    // Default fallback if no transactions exist yet
    $top_items_data = [
        ['name' => 'Adobo', 'percentage' => 35],
        ['name' => 'Sinigang', 'percentage' => 25],
        ['name' => 'Sisig', 'percentage' => 20],
        ['name' => 'Pancit', 'percentage' => 20]
    ];
}

// 6. Recent Activity Log (combination of recent transactions and low stock alerts)
$activities = [];

// Get recent transactions
$stmt = $pdo->query("SELECT id, total, created_at FROM transactions ORDER BY created_at DESC LIMIT 5");
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($recent_transactions as $tx) {
    $time = date('h:i A', strtotime($tx['created_at']));
    $activities[] = [
        'time' => $time,
        'timestamp' => strtotime($tx['created_at']),
        'action' => "Order #{$tx['id']} Completed - ₱" . number_format($tx['total'], 2),
        'status' => 'Success',
        'badge_class' => 'success'
    ];
}

// Get recent cooking activity
try {
    $stmtCook = $pdo->query("SELECT dish_name, servings_cooked, created_at FROM cooking_log ORDER BY created_at DESC LIMIT 5");
    $recent_cook = $stmtCook->fetchAll(PDO::FETCH_ASSOC);
    foreach ($recent_cook as $cook) {
        $time = date('h:i A', strtotime($cook['created_at']));
        $activities[] = [
            'time' => $time,
            'timestamp' => strtotime($cook['created_at']),
            'action' => "Cooked {$cook['servings_cooked']} servings of {$cook['dish_name']}",
            'status' => 'Success',
            'badge_class' => 'success'
        ];
    }
} catch (PDOException $e) {
    // Ignore if table doesn't exist yet
}

// Add low stock items as alerts
foreach ($low_stock_items as $item) {
    $time = date('h:i A', strtotime($item['updated_at']));
    $activities[] = [
        'time' => $time,
        'timestamp' => strtotime($item['updated_at']),
        'action' => "Stock Alert: {$item['name']} is low ({$item['quantity']} {$item['unit']} left)",
        'status' => 'Warning',
        'badge_class' => 'warning'
    ];
}

// Sort activities by timestamp descending
usort($activities, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

// Slice to top 5 recent activities
$activities = array_slice($activities, 0, 5);

// Return JSON response
echo json_encode([
    'success' => true,
    'sales_today' => $sales_today,
    'active_orders' => $tx_count_today, // number of completed transactions today
    'low_stock_count' => $low_stock_count,
    'low_stock_list' => $low_stock_list_str,
    'weekly_trend' => [
        'labels' => $weekly_labels,
        'data' => $weekly_revenue
    ],
    'top_items' => $top_items_data,
    'activities' => $activities
]);
?>
