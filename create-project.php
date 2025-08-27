<?php
require 'config.php';
session_start();

// Handle Insert / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project = trim($_POST['project'] ?? '');
    $project_id = $_POST['project_id'] ?? null;

    if ($project === '') {
        header("Location: create-project.php?error=" . urlencode("Project name is required."));
        exit;
    }

    try {
        if ($project_id) {
            $stmt = $pdo->prepare("UPDATE projects SET project = :project WHERE id = :id");
            $stmt->execute(['project' => $project, 'id' => $project_id]);
            header("Location: create-project.php?success=Project updated successfully!");
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (project) VALUES (:project)");
            $stmt->execute(['project' => $project]);
            header("Location: create-project.php?success=Project added successfully!");
        }
        exit;
    } catch (PDOException $e) {
        header("Location: create-project.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: create-project.php?success=Project deleted successfully!");
    exit;
}

// Pagination and Search
$search = trim($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 5;
$offset = ($page - 1) * $perPage;

$searchQuery = '';
$params = [];

if ($search !== '') {
    $searchQuery = " WHERE project LIKE :search";
    $params['search'] = "%$search%";
}

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM projects $searchQuery");
$totalStmt->execute($params);
$totalProjects = $totalStmt->fetchColumn();
$totalPages = ceil($totalProjects / $perPage);

$stmt = $pdo->prepare("SELECT * FROM projects $searchQuery ORDER BY id DESC LIMIT :offset, :limit");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
if ($search !== '') {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for edit
$edit_id = $_GET['edit'] ?? null;
$edit_project = null;
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_project = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sharadha Skill Academy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/adminlte.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="content-wrapper p-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><?= $edit_project ? 'Edit Project' : 'Create New Project' ?></h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
                            <?php elseif (isset($_GET['error'])): ?>
                                <div class="alert alert-danger">Error: <?= htmlspecialchars($_GET['error']) ?></div>
                            <?php endif; ?>

                            <form action="create-project.php" method="post">
                                <input type="hidden" name="project_id" value="<?= $edit_project['id'] ?? '' ?>">
                                <div class="mb-3">
                                    <label for="project" class="form-label">Project Name</label>
                                    <input type="text" name="project" id="project" class="form-control" required value="<?= htmlspecialchars($edit_project['project'] ?? '') ?>">
                                </div>
                                <button type="submit" class="btn btn-success"><?= $edit_project ? 'Update' : 'Create' ?></button>
                                <?php if ($edit_project): ?>
                                    <a href="create-project.php" class="btn btn-secondary ms-2">Cancel</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Search -->
                    <form method="get" action="create-project.php" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by Project Name">
                            <button type="submit" class="btn btn-outline-secondary">Search</button>
                            <?php if ($search): ?>
                                <a href="create-project.php" class="btn btn-outline-danger">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- List Projects -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Existing Projects</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Project Name</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($projects) > 0): ?>
                                        <?php foreach ($projects as $index => $project): ?>
                                            <tr>
                                                <td><?= ($offset + $index + 1) ?></td>
                                                <td><?= htmlspecialchars($project['project']) ?></td>
                                                <td><?= date('d M Y, h:i A', strtotime($project['created_at'])) ?></td>
                                                <td>
                                                    <a href="create-project.php?edit=<?= $project['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $project['id'] ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>

                                                    <!-- Delete Modal -->
                                                    <div class="modal fade" id="deleteModal<?= $project['id'] ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h5 class="modal-title">Delete Project</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete "<strong><?= htmlspecialchars($project['project']) ?></strong>"?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <a href="create-project.php?delete=<?= $project['id'] ?>" class="btn btn-danger">Delete</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center">No projects found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav class="p-3">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                                <a class="page-link" href="create-project.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/adminlte.js"></script>
</body>
</html>
