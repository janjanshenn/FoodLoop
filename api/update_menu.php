<?php
require_once 'db.php';

// Verify session role
$role = $_SESSION['role'] ?? '';
if ($role !== 'admin' && $role !== 'staff') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden. Unauthorized role.']);
    exit;
}

$id       = intval($_POST['id']       ?? 0);
$name     = trim($_POST['name']     ?? '');
$price    = floatval($_POST['price']    ?? 0);
$category = trim($_POST['category'] ?? 'Main Dish');
$servings = intval($_POST['servings'] ?? 10);

if ($id <= 0 || !$name || $price <= 0 || $servings < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input parameters.']);
    exit;
}

try {
    // Check if menu item exists
    $checkStmt = $pdo->prepare("SELECT id, image FROM menu WHERE id = ?");
    $checkStmt->execute([$id]);
    $menuItem = $checkStmt->fetch();

    if (!$menuItem) {
        http_response_code(404);
        echo json_encode(['error' => 'Menu item not found.']);
        exit;
    }

    $imageName = $menuItem['image']; // Default to retaining old image

    // Handle image upload if a new one is selected
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
        $newImageName = uniqid('menu_', true) . '.' . $ext;
        $destPath     = $uploadDir . $newImageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
            // Delete old image file if it exists and is not empty
            if ($imageName && file_exists($uploadDir . $imageName)) {
                @unlink($uploadDir . $imageName);
            }
            $imageName = $newImageName;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save new image.']);
            exit;
        }
    }

    // Update menu details
    $stmt = $pdo->prepare("UPDATE menu SET name = ?, price = ?, category = ?, servings = ?, image = ? WHERE id = ?");
    $stmt->execute([$name, $price, $category, $servings, $imageName, $id]);

    echo json_encode(['success' => true, 'message' => 'Menu item updated successfully.', 'image' => $imageName]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
