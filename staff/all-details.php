<?php
include '../config.php'; // your DB connection
session_start();

// Get logged-in user ID
$loggedInUserId = $_SESSION['user_id'] ?? null;

// Redirect or handle unauthenticated access
if (!$loggedInUserId) {
    die("Access denied. Please log in.");
}

// Fetch all projects
$projects = $pdo->query("SELECT id, project FROM projects")->fetchAll(PDO::FETCH_ASSOC);

// Get selected project and batch from GET parameters
$selected_project = $_GET['project_id'] ?? '';
$selected_batch = $_GET['batch_id'] ?? '';

// Fetch batches based on selected project
$batches = [];
if ($selected_project) {
    $stmt = $pdo->prepare("SELECT id, code FROM batches WHERE project_id = ?");
    $stmt->execute([$selected_project]);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch student and placement details assigned to the logged-in user
$students = [];
if ($selected_project && $selected_batch) {
    $sql = "
        SELECT 
            s.*, 
            b.code AS batch_code, 
            b.start_date, 
            p.project,

            pi.organization_name AS pi_org, 
            pi.status AS pi_status,

            ps.organization_name AS ps_org, 
            ps.status AS ps_status,

            pf.organization_name AS pf_org

        FROM students s
        JOIN batches b ON s.batch_id = b.id
        JOIN projects p ON b.project_id = p.id

        LEFT JOIN placement_initial pi ON pi.student_id = s.id
        LEFT JOIN placement_second_stage ps ON ps.student_id = s.id
        LEFT JOIN placement_final_stage pf ON pf.student_id = s.id

        WHERE s.batch_id = ?
        AND s.user_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selected_batch, $loggedInUserId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sharadha Skill Academy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link rel="preload" href="../css/adminlte.css" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="../css/adminlte.css" />
  <style>
    .filter-bar {
        margin: 1rem 0;
    }
    .table-responsive {
        overflow-x: auto;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">
  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main class="content-wrapper px-4 py-4">
    <h2 class="mb-3">Placement Overview</h2>

    <!-- Filter Bar -->
    <form method="GET" class="row g-3 filter-bar">
      <div class="col-md-4">
        <label class="form-label">Project</label>
        <select name="project_id" class="form-select" onchange="this.form.submit()">
          <option value="">Select Project</option>
          <?php foreach ($projects as $proj): ?>
            <option value="<?= $proj['id'] ?>" <?= ($proj['id'] == $selected_project) ? 'selected' : '' ?>>
              <?= htmlspecialchars($proj['project']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Batch</label>
        <select name="batch_id" class="form-select" onchange="this.form.submit()">
          <option value="">Select Batch</option>
          <?php foreach ($batches as $batch): ?>
            <option value="<?= $batch['id'] ?>" <?= ($batch['id'] == $selected_batch) ? 'selected' : '' ?>>
              <?= htmlspecialchars($batch['code']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>

    <!-- Display Student Table -->
    <?php if ($students): ?>
    <div class="table-responsive mt-4">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-light">
          <tr>
            <th>Reg No</th>
            <th>Name</th>
            <th>Gender</th>
            <th>Contact</th>
            <th>Course</th>
            <th>Batch</th>
            <th>Start Date</th>
            <th>Project</th>
            <th>Placement (Initial)</th>
            <th>Placement (Second)</th>
            <th>Placement (Final)</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['reg_no']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['gender']) ?></td>
            <td><?= htmlspecialchars($row['contact']) ?></td>
            <td><?= htmlspecialchars($row['course_name']) ?></td>
            <td><?= htmlspecialchars($row['batch_code']) ?></td>
            <td><?= htmlspecialchars($row['start_date']) ?></td>
            <td><?= htmlspecialchars($row['project']) ?></td>
            <td><?= $row['pi_org'] ? $row['pi_org'] . " ({$row['pi_status']})" : 'N/A' ?></td>
            <td><?= $row['ps_org'] ? $row['ps_org'] . " ({$row['ps_status']})" : 'N/A' ?></td>
            <td><?= $row['pf_org'] ?? 'N/A' ?></td>
            <td>
              <a href="edit-student.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary mb-1">Edit Personal</a>
              <a href="edit-step-1.php?student_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success">Edit Placement</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php elseif ($selected_project && $selected_batch): ?>
      <div class="alert alert-warning mt-4">No students found for this batch.</div>
    <?php endif; ?>
  </main>

  <?php include '../footer.php'; ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
<script src="../js/adminlte.js"></script>
</body>
</html>
