<?php
require '../config.php'; // your DB connection
$id = intval($_GET['id'] ?? 0);
$code = '';

if ($id > 0) {
  $stmt = $pdo->prepare("SELECT code FROM batches WHERE id = ?");
  $stmt->execute([$id]);
  $code = $stmt->fetchColumn() ?: '';
}

echo json_encode(['code' => $code]);
