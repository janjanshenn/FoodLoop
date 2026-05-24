<?php
require_once 'db.php';

// Check if user is logged in as admin or staff
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden. Access restricted to authorized personnel.']);
    exit;
}

try {
    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS feedback (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NULL,
        username    VARCHAR(50) NOT NULL,
        rating      INT NOT NULL,
        comments    TEXT NULL,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 1. Fetch all feedback logs
    $stmt = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Aggregate statistics
    $total_count = count($feedbacks);
    $average_rating = 0.0;
    
    // Distribution counters
    $distribution = [
        5 => 0,
        4 => 0,
        3 => 0,
        2 => 0,
        1 => 0
    ];

    if ($total_count > 0) {
        $sum_rating = 0;
        foreach ($feedbacks as $fb) {
            $r = intval($fb['rating']);
            $sum_rating += $r;
            if (isset($distribution[$r])) {
                $distribution[$r]++;
            }
        }
        $average_rating = round($sum_rating / $total_count, 1);
    }

    // Standardize percentages for UI distribution progress bars
    $dist_percentages = [];
    foreach ($distribution as $stars => $count) {
        $dist_percentages[$stars] = [
            'count' => $count,
            'percentage' => $total_count > 0 ? round(($count / $total_count) * 100) : 0
        ];
    }

    echo json_encode([
        'success' => true,
        'average_rating' => $average_rating,
        'total_count' => $total_count,
        'distribution' => $dist_percentages,
        'feedbacks' => $feedbacks
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
