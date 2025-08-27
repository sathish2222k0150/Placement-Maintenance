<?php
require '../config.php';

header('Content-Type: application/json');

if (isset($_GET['project_id'])) {
  $project_id = (int) $_GET['project_id'];

  $stmt = $pdo->prepare("SELECT id, code, start_date FROM batches WHERE project_id = ? ORDER BY code");
  $stmt->execute([$project_id]);

  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

echo json_encode([]);