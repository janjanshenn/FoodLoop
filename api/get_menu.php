<?php
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM menu ORDER BY category, name");
$menu = $stmt->fetchAll();

echo json_encode($menu);
?>
