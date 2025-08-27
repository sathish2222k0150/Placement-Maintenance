<?php
require __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Access denied. Please login.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all projects
$projects = $pdo->query("SELECT id, project FROM projects ORDER BY project")->fetchAll(PDO::FETCH_ASSOC);

$selected_project = $_GET['project_id'] ?? '';
$selected_batch = $_GET['batch_id'] ?? '';

// Fetch batches for selected project
$batches = [];
if ($selected_project) {
    $stmt = $pdo->prepare("SELECT id, code FROM batches WHERE project_id = ?");
    $stmt->execute([$selected_project]);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch students for selected batch and user_id, who do not have placement record
$students = [];
if ($selected_batch) {
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.reg_no, s.course_name
        FROM students s
        LEFT JOIN placement_initial p ON s.id = p.student_id
        WHERE s.batch_id = ? AND s.user_id = ? AND p.student_id IS NULL
    ");
    $stmt->execute([$selected_batch, $user_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sharadha Skill Academy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="supported-color-schemes" content="light dark" />

  <!-- Stylesheets -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
 <link rel="preload" href="../css/adminlte.css" as="style" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print" onload="this.media='all'" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../css/adminlte.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous" />
  
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">

    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="content-wrapper p-4">
      <div class="container-fluid">
        <h3 class="mb-4">Select Assigned Student</h3>

        <!-- Filter Form -->
        <form method="GET" class="card p-4 shadow-sm mb-4" style="max-width: 700px;">
          <div class="mb-3">
            <label for="project_id" class="form-label">Select Project</label>
            <select name="project_id" id="project_id" class="form-select" required onchange="this.form.submit()">
              <option value="">-- Select Project --</option>
              <?php foreach ($projects as $project): ?>
                <option value="<?= $project['id'] ?>" <?= ($selected_project == $project['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($project['project']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php if ($batches): ?>
          <div class="mb-3">
            <label for="batch_id" class="form-label">Select Batch</label>
            <select name="batch_id" id="batch_id" class="form-select" required onchange="this.form.submit()">
              <option value="">-- Select Batch --</option>
              <?php foreach ($batches as $batch): ?>
                <option value="<?= $batch['id'] ?>" <?= ($selected_batch == $batch['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($batch['code']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
        </form>

        <!-- Student Select Form -->
        <?php if ($selected_batch): ?>
          <?php if (count($students) > 0): ?>
            <form action="step-1.php" method="GET" class="card p-4 shadow-sm" style="max-width: 700px;">
              <div class="mb-3">
                <label for="student" class="form-label">Student (No Placement Created)</label>
                <select name="student_id" id="student" class="form-select" required>
                  <option value="">-- Select a Student --</option>
                  <?php foreach ($students as $student): ?>
                    <option value="<?= $student['id'] ?>">
                      <?= htmlspecialchars($student['name']) ?> (<?= $student['reg_no'] ?> - <?= $student['course_name'] ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Proceed to Placement Step 1</button>
            </form>
          <?php else: ?>
            <div class="alert alert-warning">No eligible students without placement in this batch.</div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <?php include '../footer.php'; ?>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
