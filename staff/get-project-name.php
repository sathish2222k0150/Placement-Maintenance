<?php
require '../config.php'; // your DB connection
$id = intval($_GET['id'] ?? 0);
$name = '';

if ($id > 0) {
  $stmt = $pdo->prepare("SELECT project FROM projects WHERE id = ?");
  $stmt->execute([$id]);
  $name = $stmt->fetchColumn() ?: '';
}

echo json_encode(['name' => $name]);
