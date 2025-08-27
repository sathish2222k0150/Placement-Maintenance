<?php
include 'config.php'; // Contains your PDO connection ($pdo)
session_start(); // Start the session to access user_id
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
// Filter inputs
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$gender = $_GET['gender'] ?? '';
$caste = $_GET['caste'] ?? '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch distinct caste categories
$casteOptionsStmt = $pdo->query("SELECT DISTINCT caste FROM students WHERE caste IS NOT NULL AND caste != '' ORDER BY caste ASC");
$casteOptions = $casteOptionsStmt->fetchAll(PDO::FETCH_COLUMN);

// Base query
$where = " WHERE 1=1 ";
$params = [];

if ($startDate && $endDate) {
    $where .= " AND DATE(created_at) BETWEEN :start AND :end";
    $params[':start'] = $startDate;
    $params[':end'] = $endDate;
}

if (!empty($gender)) {
    $where .= " AND gender = :gender";
    $params[':gender'] = $gender;
}

if (!empty($caste)) {
    $where .= " AND caste = :caste";
    $params[':caste'] = $caste;
}

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM students $where");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();

// Handle export
if (isset($_GET['export']) && $_GET['export'] == '1') {
    $exportStmt = $pdo->prepare("SELECT * FROM students $where ORDER BY created_at DESC");
    $exportStmt->execute($params);
    $exportData = $exportStmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="filtered_students.csv"');
    $output = fopen("php://output", "w");
    fputcsv($output, ['Reg No', 'Name', 'Gender', 'Caste', 'Created At']);
    foreach ($exportData as $row) {
        fputcsv($output, [$row['reg_no'], $row['name'], $row['gender'], $row['caste'], $row['created_at']]);
    }
    fclose($output);
    exit;
}

// Fetch paginated data
$dataStmt = $pdo->prepare("SELECT * FROM students $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $dataStmt->bindValue($key, $value);
}
$dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$dataStmt->execute();
$students = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($totalRecords / $limit);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Sharadha Skill Academy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="./css/adminlte.css" />
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        <?php include 'header.php'; ?>
        <?php include 'sidebar.php'; ?>

        <div class="container mt-4">
            <h4 class="mb-3">Filter Students by Gender, Caste, and Date</h4>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date:</label>
                    <input type="date" name="start_date" class="form-control"
                        value="<?= htmlspecialchars($startDate) ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>"
                        required>
                </div>
                <div class="col-md-2">
                    <label for="gender" class="form-label">Gender:</label>
                    <select name="gender" class="form-select">
                        <option value="">All</option>
                        <option value="Male" <?= $gender == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $gender == 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="caste" class="form-label">Caste Category:</label>
                    <select name="caste" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($casteOptions as $casteOption): ?>
                            <option value="<?= htmlspecialchars($casteOption) ?>" <?= $casteOption == $caste ? 'selected' : '' ?>>
                                <?= htmlspecialchars($casteOption) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <?php if ($totalRecords > 0): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['export' => '1'])) ?>"
                            class="btn btn-success">Export</a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($startDate && $endDate): ?>
                <div class="mt-4">
                    <h5>Filtered Results (<?= $totalRecords ?> students found)</h5>
                    <?php if ($students): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mt-2">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Reg No</th>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Caste</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $s): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($s['reg_no']) ?></td>
                                            <td><?= htmlspecialchars($s['name']) ?></td>
                                            <td><?= htmlspecialchars($s['gender']) ?></td>
                                            <td><?= htmlspecialchars($s['caste']) ?></td>
                                            <td><?= htmlspecialchars($s['created_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav class="mt-3">
                            <div class="d-flex justify-content-center">
                                <ul class="pagination">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link"
                                                href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </div>
                        </nav>

                    <?php else: ?>
                        <p class="text-danger mt-3">No records found for given filters.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/adminlte.js"></script>
</body>

</html>