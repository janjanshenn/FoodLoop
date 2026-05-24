<?php
require_once 'db.php';
require_role(['admin', 'staff']);

$name     = trim($_POST['name']     ?? '');
$price    = floatval($_POST['price']    ?? 0);
$category = trim($_POST['category'] ?? 'Main Dish');
$servings = intval($_POST['servings'] ?? 10);

if (!$name || $price <= 0 || $servings < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input parameters.']);
    exit;
}

// Handle image upload
$imageName = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Image upload failed with error code: ' . $_FILES['image']['error']]);
        exit;
    }

    $uploadDir = '../uploads/';

    // Create folder if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid image type. Use jpg, png, webp, or gif.']);
        exit;
    }

    // Create unique filename to avoid collisions
    $imageName = uniqid('menu_', true) . '.' . $ext;
    $destPath  = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save image.']);
        exit;
    }
}

$stmt = $pdo->prepare("INSERT INTO menu (name, price, category, image, servings) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$name, $price, $category, $imageName, $servings]);


echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'image' => $imageName]);
?>
