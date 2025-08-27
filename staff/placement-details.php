<?php
include '../config.php';
session_start();

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Fetch all projects
$projects = $pdo->query("SELECT * FROM projects")->fetchAll(PDO::FETCH_ASSOC);

// Filter input
$project_id = $_GET['project_id'] ?? '';
$batch_id = $_GET['batch_id'] ?? '';

// Fetch batches based on selected project
$batches = [];
if ($project_id) {
    $stmt = $pdo->prepare("SELECT * FROM batches WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Build dynamic SQL
$where = "WHERE pi.student_id IS NOT NULL AND s.user_id = ?";
$params = [$_SESSION['user_id']];

if ($project_id) {
    $where .= " AND b.project_id = ?";
    $params[] = $project_id;
}
if ($batch_id) {
    $where .= " AND s.batch_id = ?";
    $params[] = $batch_id;
}

$sql = "SELECT 
            s.*, 
            b.code AS batch_code, 
            b.start_date, 
            p.project AS project_name
        FROM students s
        INNER JOIN placement_initial pi ON pi.student_id = s.id
        LEFT JOIN batches b ON s.batch_id = b.id
        LEFT JOIN projects p ON b.project_id = p.id
        $where
        ORDER BY s.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Placed Students</title>
    <link rel="preload" href="../css/adminlte.css" as="style" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="../css/adminlte.css" />
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="content-wrapper p-4">
        <div class="container-fluid">
            <h3 class="mb-4">View Placed Students</h3>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="project_id" class="form-label">Project</label>
                            <select name="project_id" id="project_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Select Project</option>
                                <?php foreach ($projects as $proj): ?>
                                    <option value="<?= $proj['id'] ?>" <?= ($proj['id'] == $project_id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($proj['project']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="batch_id" class="form-label">Batch</label>
                            <select name="batch_id" id="batch_id" class="form-select">
                                <option value="">Select Batch</option>
                                <?php foreach ($batches as $batch): ?>
                                    <option value="<?= $batch['id'] ?>" <?= ($batch['id'] == $batch_id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($batch['code']) ?> (<?= $batch['start_date'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="placement-details.php" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white fw-bold">Placed Students List</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Project</th>
                                <th>Batch Code</th>
                                <th>Start Date</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($students)): ?>
                                <?php foreach ($students as $stu): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($stu['name']) ?></td>
                                        <td><?= htmlspecialchars($stu['project_name']) ?></td>
                                        <td><?= htmlspecialchars($stu['batch_code']) ?></td>
                                        <td><?= htmlspecialchars($stu['start_date']) ?></td>
                                        <td>
                                            <a href="edit-step-1.php?student_id=<?= $stu['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No placed students found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
<script src="../js/adminlte.js"></script>
</body>
</html>
