<?php
require 'config.php';
session_start();

// Handle create or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $project_id = intval($_POST['project_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $batch_id = intval($_POST['batch_id'] ?? 0);

    if ($code === '' || !$project_id || $start_date === '') {
        header("Location: create-batch.php?error=" . urlencode("All fields are required."));
        exit;
    }

    try {
        if ($batch_id > 0) {
            $stmt = $pdo->prepare("UPDATE batches SET code = :code, project_id = :project_id, start_date = :start_date WHERE id = :id");
            $stmt->execute([
                'code' => $code,
                'project_id' => $project_id,
                'start_date' => $start_date,
                'id' => $batch_id
            ]);
            header("Location: create-batch.php?success=" . urlencode("Batch updated successfully."));
        } else {
            $stmt = $pdo->prepare("INSERT INTO batches (code, project_id, start_date) VALUES (:code, :project_id, :start_date)");
            $stmt->execute([
                'code' => $code,
                'project_id' => $project_id,
                'start_date' => $start_date
            ]);
            header("Location: create-batch.php?success=" . urlencode("Batch created successfully."));
        }
        exit;
    } catch (PDOException $e) {
        header("Location: create-batch.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM batches WHERE id = ?")->execute([$id]);
    header("Location: create-batch.php?success=" . urlencode("Batch deleted."));
    exit;
}

// Get edit info
$edit_id = $_GET['edit'] ?? null;
$edit_batch = null;
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM batches WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_batch = $stmt->fetch();
}

// Fetch projects
$projects = $pdo->query("SELECT id, project FROM projects ORDER BY project ASC")->fetchAll();

// Fetch recent batches (limit to 10)
$batches = $pdo->query("
    SELECT b.*, p.project FROM batches b 
    JOIN projects p ON b.project_id = p.id 
    ORDER BY b.created_at DESC LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Manage Batches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/adminlte.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <h3 class="mb-4"><?= $edit_batch ? "Edit Batch" : "Create New Batch" ?></h3>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <!-- Batch Form -->
        <form method="post" class="card p-4 mb-4 shadow-sm">
            <input type="hidden" name="batch_id" value="<?= $edit_batch['id'] ?? '' ?>">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Center Name and Batch Code</label>
                    <input type="text" name="code" class="form-control" required
                        value="<?= htmlspecialchars($edit_batch['code'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select" required>
                        <option value="">Select Project</option>
                        <?php foreach ($projects as $proj): ?>
                            <option value="<?= $proj['id'] ?>" <?= isset($edit_batch['project_id']) && $proj['id'] == $edit_batch['project_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($proj['project']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required
                        value="<?= htmlspecialchars($edit_batch['start_date'] ?? '') ?>">
                </div>
            </div>
            <div class="text-center my-3">
                <button class="btn btn-success" style="width: 200px;">
                    <?= $edit_batch ? 'Update Batch' : 'Create Batch' ?>
                </button>
            </div>

            <?php if ($edit_batch): ?>
                <div class="text-center mb-4">
                    <a href="create-batch.php" class="btn btn-secondary" style="width: 200px;">Cancel</a>
                </div>
            <?php endif; ?>
        </form>

        <!-- View All Batches Button Above Table -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Recent Batches</h5>
            <a href="show-batches.php" class="btn btn-primary btn-sm">View All Batches</a>
        </div>

        <!-- Batch List -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                Recent Batches
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>S.No</th>
                            <th>Code</th>
                            <th>Project</th>
                            <th>Start Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($batches): ?>
                            <?php foreach ($batches as $index => $batch): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($batch['code']) ?></td>
                                    <td><?= htmlspecialchars($batch['project']) ?></td>
                                    <td><?= htmlspecialchars($batch['start_date']) ?></td>
                                    <td>
                                        <a href="?edit=<?= $batch['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal<?= $batch['id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?= $batch['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete batch
                                                        <strong><?= htmlspecialchars($batch['code']) ?></strong>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <a href="?delete=<?= $batch['id'] ?>" class="btn btn-danger">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No batches found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

        <?php include 'footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
        <script src="./js/adminlte.js"></script>
</body>

</html>