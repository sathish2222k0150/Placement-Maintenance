<?php
require '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

// Uniqueness check endpoint
if (isset($_GET['check_unique'])) {
  $field = $_GET['field'];
  $value = $_GET['value'];

  $allowedFields = ['aadhar', 'reg_no'];
  if (!in_array($field, $allowedFields)) {
    echo json_encode(['error' => 'Invalid field']);
    exit;
  }

  $sql = "SELECT COUNT(*) FROM students WHERE $field = ?";
  $check = $pdo->prepare($sql);
  $check->execute([$value]);
  echo json_encode(['exists' => $check->fetchColumn() > 0]);
  exit;
}

// Get current user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$course = $user['course'] ?? '';

// Multi-step control
$step = isset($_GET['step']) ? max(1, min(5, intval($_GET['step']))) : 1;
$form = [];

if (isset($_SESSION['student_form']) && isset($_POST['save_draft'])) {
  // If user saved a draft, keep session
  $form = $_SESSION['student_form'];
} elseif (isset($_SESSION['student_form']) && isset($_GET['step'])) {
  // User is navigating back/forward during add
  $form = $_SESSION['student_form'];
} else {
  // Clear old session if new entry begins without draft/save
  unset($_SESSION['student_form']);
  $form = []; // start fresh
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['student_form'] = array_merge($form, $_POST);

  if (isset($_POST['save_draft'])) {
    echo "<script>alert('Draft saved.');location='?step={$step}';</script>";
    exit;
  }

  if ($step < 5) {
    header("Location: ?step=".($step + 1));
    exit;
  } else {
    // Final submission
    
    // Uniqueness checks
    $reg_no = $_SESSION['student_form']['reg_no'] ?? '';
    $aadhar = $_SESSION['student_form']['aadhar'] ?? '';

    $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE reg_no = ?");
    $check->execute([$reg_no]);
    if ($check->fetchColumn() > 0) {
      echo "<script>alert('Error: Registration number already exists.');location='?step=5';</script>";
      exit;
    }

    $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE aadhar = ?");
    $check->execute([$aadhar]);
    if ($check->fetchColumn() > 0) {
      echo "<script>alert('Error: Aadhar number already exists.');location='?step=5';</script>";
      exit;
    }

    // Prepare insert data
    $d = $_SESSION['student_form'];
    $manualQualifications = ['ITI', 'Diploma', 'Graduate', 'Post Graduate', 'B.E/B.Tech', 'Others'];
    $selectedQualification = $d['qualification'] ?? '';
    $qualificationDetail = trim($d['qualification_detail'] ?? '');

    if (in_array($selectedQualification, $manualQualifications) && $qualificationDetail !== '') {
      $d['qualification'] = $selectedQualification . ' - ' . $qualificationDetail;
    }

    if (($d['caste'] ?? '') === 'Others' && !empty($d['caste_detail'])) {
      $d['caste'] = 'Others - ' . trim($d['caste_detail']);
    }

    // Save to DB
    $stmt2 = $pdo->prepare(
      "INSERT INTO students (
        batch_id, course_name, state,
        reg_no, aadhar, boarding_lodging, name, father_or_husband_name, gender, dob,
        qualification, religion, caste, annual_income, address, village, mandal, district,
        contact, alt_contact, batch_end, user_id
      ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );

    $stmt2->execute([
      $d['batch_id'] ?? null,
      $course,
      $d['state'] ?? 'Tamil Nadu',
      $d['reg_no'] ?? '',
      $d['aadhar'] ?? '',
      $d['boarding_lodging'] ?? '',
      $d['name'] ?? '',
      $d['father_or_husband_name'] ?? '',
      $d['gender'] ?? '',
      $d['dob'] ?? null,
      $d['qualification'] ?? '',
      $d['religion'] ?? '',
      $d['caste'] ?? '',
      $d['annual_income'] ?? '',
      $d['address'] ?? '',
      $d['village'] ?? '',
      $d['mandal'] ?? '',
      $d['district'] ?? '',
      $d['contact'] ?? '',
      $d['alt_contact'] ?? '',
      $d['batch_end'] ?? null,
      $user_id
    ]);

    unset($_SESSION['student_form']);
    echo "<script>alert('Successfully registered!');location='student-details.php';</script>";
    exit;
  }
}

$projects = $pdo->query("SELECT id, project FROM projects ORDER BY project")->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Sharadha Skill Academy</title>
  <link rel="preload" href="../css/adminlte.css" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
    onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
    crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />
  <link rel="stylesheet" href="../css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
    integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous" />
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6 mt-3">
              <h1 class="m-0">Add Student - Step <?= intval($step) ?></h1>
            </div>
          </div>
        </div>
      </div>

      <section class="content">
        <div class="container-fluid">
          <!-- Card -->
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h3 class="card-title">Multi-step Registration Form</h3>
            </div>
            <div class="card-body">
              <!-- (Optional) Simple progress indicator -->
              <div class="progress mb-4" style="height: 10px;">
                <?php $percent = ($step / 5) * 100; ?>
                <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%;"
                  aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
              </div>

              <form method="post" id="regForm">
                <?php include "steps/step$step.php"; ?>

                <div class="mt-3 d-flex justify-content-between">
                  <div>
                    <?php if ($step > 1): ?>
                      <a href="?step=<?= intval($step - 1) ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                      </a>
                    <?php endif; ?>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="submit" name="save_draft" class="btn btn-warning">
                      <i class="bi bi-save"></i> Save Draft
                    </button>

                    <?php if ($step < 5): ?>
                      <button type="submit" class="btn btn-primary">
                        Next <i class="bi bi-arrow-right"></i>
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-success" onclick="openPreview()">
                        <i class="bi bi-eye"></i> Preview &amp; Submit
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="previewContent" class="p-2"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-pencil-square"></i> Edit
            </button>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('regForm').submit()">
              <i class="bi bi-check2-circle"></i> Confirm &amp; Submit
            </button>
          </div>
        </div>
      </div>
    </div>

    <?php include '../footer.php'; ?>
  </div>
  <script src="./js/form.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="../js/adminlte.js"></script>
  <script>
    window.studentFormData = <?= json_encode($form ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  </script>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
</body>

</html>