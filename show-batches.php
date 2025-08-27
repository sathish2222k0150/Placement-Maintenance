<?php
require 'config.php';
session_start();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM batches WHERE id = ?")->execute([$id]);
    header("Location: show-batches.php?success=" . urlencode("Batch deleted."));
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

// Pagination
$limit = 10; // Batches per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = $_GET['search'] ?? '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE b.code LIKE ? OR p.project LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Count total records
$count_sql = "
    SELECT COUNT(*) FROM batches b 
    JOIN projects p ON b.project_id = p.id 
    $where
";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_batches = $count_stmt->fetchColumn();
$total_pages = ceil($total_batches / $limit);

// Fetch paginated batches
$sql = "
    SELECT b.*, p.project FROM batches b 
    JOIN projects p ON b.project_id = p.id 
    $where
    ORDER BY b.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$batches = $stmt->fetchAll();

// Fetch projects for filter dropdown
$projects = $pdo->query("SELECT id, project FROM projects ORDER BY project ASC")->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>All Batches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/adminlte.css" />
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        <?php include 'header.php'; ?>
        <?php include 'sidebar.php'; ?>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>All Batches</h3>
                <a href="create-batch.php" class="btn btn-success">Create New Batch</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <!-- Search and Filter Form -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" placeholder="Search by batch code or project..." 
                                value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-2">
                            <a href="show-batches.php" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Batch List -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
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
                                            <td><?= ($offset + $index + 1) ?></td>
                                            <td><?= htmlspecialchars($batch['code']) ?></td>
                                            <td><?= htmlspecialchars($batch['project']) ?></td>
                                            <td><?= htmlspecialchars($batch['start_date']) ?></td>
                                            <td>
                                                <a href="create-batch.php?edit=<?= $batch['id'] ?>" class="btn btn-sm btn-warning">
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
                                                                <a href="show-batches.php?delete=<?= $batch['id'] ?>" class="btn btn-danger">Delete</a>
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

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav class="mt-3">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
        <script src="./js/adminlte.js"></script>
    </div>
</body>

</html>
