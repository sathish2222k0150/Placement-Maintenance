<?php
require '../config.php'; // include DB connection
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Handle bulk delete
if (isset($_POST['bulk_delete']) && isset($_POST['selected_students'])) {
    $ids = implode(',', array_map('intval', $_POST['selected_students']));
    $stmt = $pdo->prepare("DELETE FROM students WHERE id IN ($ids)");
    $stmt->execute();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle single delete
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Search/filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_name = isset($_GET['filter_name']) ? $_GET['filter_name'] : '';
$filter_gender = isset($_GET['filter_gender']) ? $_GET['filter_gender'] : '';
$filter_qualification = isset($_GET['filter_qualification']) ? $_GET['filter_qualification'] : '';
$filter_contact = isset($_GET['filter_contact']) ? $_GET['filter_contact'] : '';

// Build WHERE clause for filters
$where = ["user_id = ?"];
$params = [$_SESSION['user_id']];

if (!empty($filter_name)) {
    $where[] = "name LIKE ?";
    $params[] = "%$filter_name%";
}
if (!empty($filter_gender)) {
    $where[] = "gender = ?";
    $params[] = $filter_gender;
}
if (!empty($filter_qualification)) {
    $where[] = "qualification LIKE ?";
    $params[] = "%$filter_qualification%";
}
if (!empty($filter_contact)) {
    $where[] = "(contact LIKE ? OR alt_contact LIKE ?)";
    $params[] = "%$filter_contact%";
    $params[] = "%$filter_contact%";
}
if (!empty($search)) {
    $where[] = "(name LIKE ? OR contact LIKE ? OR qualification LIKE ? OR gender LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total students with filters
$count_query = "SELECT COUNT(*) as total FROM students $where_clause";
$totalQuery = $pdo->prepare($count_query);
$totalQuery->execute($params);
$totalStudents = $totalQuery->fetch()['total'];
$totalPages = ceil($totalStudents / $limit);

// Fetch student records with filters
$query = "SELECT * FROM students $where_clause ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $pdo->prepare($query);
$result->execute($params);
?>

<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sharadha Skill Academy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <meta name="title" content="AdminLTE | Dashboard v2" />
    <meta name="author" content="ColorlibHQ" />
    <meta name="description" content="AdminLTE is a Free Bootstrap 5 Admin Dashboard" />
    <meta name="keywords" content="bootstrap 5, admin dashboard, datatable, student records" />
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../css/adminlte.css" as="style" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        crossorigin="anonymous" media="print" onload="this.media='all'" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="../css/adminlte.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
        crossorigin="anonymous" />
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        <?php include 'header.php'; ?>
        <?php include 'sidebar.php'; ?>

        <!-- Main content -->
        <div class="content-wrapper p-4">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Student Details</h5>
                </div>

                <!-- Search and Filter Bar -->
                <div class="card-body border-bottom">
                    <form method="get" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="filter_name" class="form-control" placeholder="Filter by Name"
                                    value="<?= htmlspecialchars($filter_name) ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="filter_gender" class="form-select">
                                    <option value="">All Genders</option>
                                    <option value="Male" <?= $filter_gender == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $filter_gender == 'Female' ? 'selected' : '' ?>>Female
                                    </option>
                                    <option value="Other" <?= $filter_gender == 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="filter_qualification" class="form-control"
                                    placeholder="Filter by Qualification"
                                    value="<?= htmlspecialchars($filter_qualification) ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="filter_contact" class="form-control"
                                    placeholder="Filter by Contact" value="<?= htmlspecialchars($filter_contact) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <form method="get" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by Name, Gender, Qualification, Contact..."
                                value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                            <?php if (!empty($search) || !empty($filter_name) || !empty($filter_gender) || !empty($filter_qualification) || !empty($filter_contact)): ?>
                                <a href="student-details.php" class="btn btn-outline-danger">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Bulk Actions -->
                <form method="post" id="bulkForm">
                    <div class="card-body border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" name="bulk_delete" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete selected students?')">
                                    <i class="bi bi-trash"></i> Delete Selected
                                </button>
                            </div>
                            <div class="text-muted">
                                Total Records: <?= $totalStudents ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <div style="overflow-x: auto;">
                            <table class="table table-striped table-hover table-bordered text-nowrap">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40"><input type="checkbox" id="selectAll"></th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Qualification</th>
                                        <th>Contact</th>
                                        <th>Course</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->rowCount() > 0): ?>
                                        <?php while ($row = $result->fetch()): ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected_students[]" value="<?= $row['id'] ?>">
                                                </td>
                                                <td><?= htmlspecialchars($row['id']) ?></td>
                                                <td><?= htmlspecialchars($row['name']) ?></td>
                                                <td><?= htmlspecialchars($row['gender']) ?></td>
                                                <td><?= htmlspecialchars($row['qualification']) ?></td>
                                                <td><?= htmlspecialchars($row['contact']) ?></td>
                                                <td><?= htmlspecialchars($row['course_name']) ?></td>
                                                <td>
                                                    <a href="edit-student.php?edit=<?= $row['id'] ?>"
                                                        class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this student?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-info" title="View"
                    onclick="viewStudentDetails(<?= $row['id'] ?>)">
                    <i class="bi bi-eye"></i>
                </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No students found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>

                <!-- Pagination -->
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing <?= ($offset + 1) ?> to <?= min($offset + $limit, $totalStudents) ?> of
                        <?= $totalStudents ?> entries
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link"
                                        href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">First</a></li>
                                <li class="page-item"><a class="page-link"
                                        href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);

                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }

                            for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link"
                                        href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item"><a class="page-link"
                                        href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item"><a class="page-link"
                                        href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                                </li>
                                <li class="page-item"><a class="page-link"
                                        href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">Last</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Student Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="previewContent" class="p-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php include '../footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
        crossorigin="anonymous"></script>
    <script src="../js/adminlte.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
        crossorigin="anonymous"></script>
    <script>
          function viewStudentDetails(studentId) {
    fetch('get-student.php?id=' + studentId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data || Object.keys(data).length === 0) {
                throw new Error('No student data received');
            }

            let html = '<div class="table-responsive"><table class="table table-bordered table-striped">';
            
            // Define fields to skip
            const skipFields = ['id', 'user_id', 'qualification_detail', 'caste_detail', 
                              'project_id', 'batch_id', 'created_at', 'updated_at'];
            
            // Special handling fields
            const manualQualifyKeys = ['ITI', 'Diploma', 'Graduate', 'Post Graduate', 'B.E/B.Tech', 'Others'];
            const qualificationValue = data['qualification'] || '';
            const qualificationDetail = data['qualification_detail'] || '';
            const casteValue = data['caste'] || '';
            const casteDetail = data['caste_detail'] || '';

            // Display project information first if available
            if (data['project_name']) {
                html += `<tr><th style="width:30%">Project</th><td>${data['project_name']}</td></tr>`;
            }

            // Display batch information if available
            if (data['batch_code']) {
                html += `<tr><th style="width:30%">Batch Code</th><td>${data['batch_code']}</td></tr>`;
                if (data['batch_start_date']) {
                    const formattedDate = new Date(data['batch_start_date']).toLocaleDateString();
                    html += `<tr><th style="width:30%">Batch Start Date</th><td>${formattedDate}</td></tr>`;
                }
            }

            // Process each field
            for (let key in data) {
                if (skipFields.includes(key) || 
                    key === 'project_name' || 
                    key === 'batch_code' || 
                    key === 'batch_start_date') continue;
                
                let label = key.replace(/_/g, ' ')
                              .replace(/\b\w/g, s => s.toUpperCase());
                let value = data[key] || '';
                
                // Special cases
                if (key === 'qualification' && manualQualifyKeys.includes(qualificationValue) && qualificationDetail) {
                    value = `${qualificationValue} - ${qualificationDetail}`;
                }
                if (key === 'caste' && casteValue === 'Others' && casteDetail) {
                    value = `Others - ${casteDetail}`;
                }
                
                if (value) {
                    html += `<tr><th style="width:30%">${label}</th><td>${value}</td></tr>`;
                }
            }
            
            html += '</table></div>';
            document.getElementById('previewContent').innerHTML = html;
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load student details. Please try again.');
        });
}

        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_students[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Confirm before bulk delete
        document.getElementById('bulkForm').addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('input[name="selected_students[]"]:checked').length;
            if (checked === 0) {
                e.preventDefault();
                alert('Please select at least one student to delete.');
            }
        });
    </script>
</body>
</html>