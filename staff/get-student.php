<?php
require '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = [];

if ($id > 0) {
    // Get student data with batch and project information
    $stmt = $pdo->prepare("SELECT s.*, b.code as batch_code, b.start_date as batch_start_date, 
                          p.project as project_name
                          FROM students s
                          LEFT JOIN batches b ON s.batch_id = b.id
                          LEFT JOIN projects p ON b.project_id = p.id
                          WHERE s.id = :id");
    $stmt->execute(['id' => $id]);
    $response = $stmt->fetch(PDO::FETCH_ASSOC);
}

header('Content-Type: application/json');
echo json_encode($response);
?>