<?php
require_once 'db.php';

// Decode POST JSON data
$data = json_decode(file_get_contents('php://input'), true);

$username = trim($data['username'] ?? '');
$rating   = intval($data['rating'] ?? 0);
$comments = trim($data['comments'] ?? '');

// If username is empty and user is logged in, use session username
if (empty($username) && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

// Validation
if (empty($username)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please provide a username/name.']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Rating must be between 1 and 5 stars.']);
    exit;
}

try {
    // 1. Ensure feedback table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS feedback (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NULL,
        username    VARCHAR(50) NOT NULL,
        rating      INT NOT NULL,
        comments    TEXT NULL,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 2. Resolve user_id if logged in
    $userId = null;
    if (isset($_SESSION['username'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['username']]);
        $user = $stmt->fetch();
        if ($user) {
            $userId = $user['id'];
        }
    }

    // 3. Insert feedback record
    $stmt = $pdo->prepare("INSERT INTO feedback (user_id, username, rating, comments, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $username, $rating, $comments]);

    echo json_encode([
        'success' => true,
        'message' => 'Feedback submitted successfully. Thank you!'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
