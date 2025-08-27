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
    $edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;

    $allowedFields = ['aadhar', 'reg_no'];
    if (!in_array($field, $allowedFields)) {
        echo json_encode(['error' => 'Invalid field']);
        exit;
    }

    $sql = "SELECT COUNT(*) FROM students WHERE $field = ?";
    $params = [$value];
    if ($edit_id > 0) {
        $sql .= " AND id != ?";
        $params[] = $edit_id;
    }

    $check = $pdo->prepare($sql);
    $check->execute($params);
    echo json_encode(['exists' => $check->fetchColumn() > 0]);
    exit;
}

// Get current user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$course = $user['course'] ?? '';

// Edit state check
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$existing_data = [];

if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? AND user_id = ?");
    $stmt->execute([$edit_id, $user_id]);
    $existing_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing_data) {
        // If student doesn't exist or doesn't belong to this user, redirect
        unset($_SESSION['student_form']); // Clear any stale session data
        header('Location: student-details.php');
        exit;
    }

    // Fetch project_id from batch
    if (!empty($existing_data['batch_id'])) {
        $batchStmt = $pdo->prepare("SELECT b.id AS batch_id, b.code, b.start_date, b.project_id, p.project AS project_name 
                                  FROM batches b 
                                  JOIN projects p ON b.project_id = p.id 
                                  WHERE b.id = ?");
        $batchStmt->execute([$existing_data['batch_id']]);
        $batchData = $batchStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($batchData) {
            $existing_data['batch_id'] = $batchData['batch_id'];
            $existing_data['batch_code'] = $batchData['code'];
            $existing_data['batch_start'] = $batchData['start_date'];
            $existing_data['project_id'] = $batchData['project_id'];
            $existing_data['project_name'] = $batchData['project_name'];
        }
    }

    // Unpack composite 'qualification' field for the form
    if (isset($existing_data['qualification'])) {
        $qualParts = explode(' - ', $existing_data['qualification'], 2);
        if (count($qualParts) > 1) {
            $existing_data['qualification'] = $qualParts[0];
            $existing_data['qualification_detail'] = $qualParts[1];
        }
    }

    // Unpack composite 'caste' field for the form
    if (isset($existing_data['caste']) && strpos($existing_data['caste'], 'Others - ') === 0) {
        // Extract the detail part and set the main caste to 'Others'
        $existing_data['caste_detail'] = substr($existing_data['caste'], 9); // Length of "Others - " is 9
        $existing_data['caste'] = 'Others';
    }
}

// Multi-step control
$step = isset($_GET['step']) ? max(1, min(5, intval($_GET['step']))) : 1;

// ------------------- START OF FIX ------------------- //
// Form Initialization
if ($edit_id > 0) {
    // We are in EDIT mode.
    // Check if the session is for a DIFFERENT student or doesn't exist.
    // This is the key: we start fresh if the ID in the URL doesn't match the ID in the session.
    if (!isset($_SESSION['student_form']) || empty($_SESSION['student_form']) || ($_SESSION['student_form']['id'] ?? 0) != $edit_id) {
        // This is a new edit session for this student.
        // Load the complete record from the database into the session.
        // This ensures all steps are pre-populated with existing data from the start.
        $_SESSION['student_form'] = $existing_data;
    }
}
// Load the form data from the session. 
// This will either be the freshly loaded data (from above) or the data from a previous step in the same edit session.
$form = $_SESSION['student_form'] ?? [];
// -------------------- END OF FIX -------------------- //


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Merge POST data with existing session form data
    $form = array_merge($form, $_POST);
    $_SESSION['student_form'] = $form;

    if (isset($_POST['save_draft'])) {
        echo "<script>alert('Draft saved.');location='?step={$step}&edit=$edit_id';</script>";
        exit;
    }

    if ($step < 5) {
        header("Location: ?step=".($step + 1)."&edit=$edit_id");
        exit;
    } else {
        // Final submission (Step 5)
        
        // Get the original registration number and aadhar for comparison
        $original_reg_no = $existing_data['reg_no'] ?? '';
        $original_aadhar = $existing_data['aadhar'] ?? '';
        $reg_no = $form['reg_no'] ?? '';
        $aadhar = $form['aadhar'] ?? '';

        // Only check uniqueness if the value has changed
        if ($reg_no !== $original_reg_no) {
            $check_sql = "SELECT COUNT(*) FROM students WHERE reg_no = ? AND id != ?";
            $check = $pdo->prepare($check_sql);
            $check->execute([$reg_no, $edit_id]);
            if ($check->fetchColumn() > 0) {
                echo "<script>alert('Error: Registration number already exists.');location='?step=5&edit=$edit_id';</script>";
                exit;
            }
        }

        if ($aadhar !== $original_aadhar) {
            $check_sql = "SELECT COUNT(*) FROM students WHERE aadhar = ? AND id != ?";
            $check = $pdo->prepare($check_sql);
            $check->execute([$aadhar, $edit_id]);
            if ($check->fetchColumn() > 0) {
                echo "<script>alert('Error: Aadhar number already exists.');location='?step=5&edit=$edit_id';</script>";
                exit;
            }
        }

        // Prepare update data
        $d = $form;
        
        // Re-combine qualification field
        $manualQualifications = ['ITI', 'Diploma', 'Graduate', 'Post Graduate', 'B.E/B.Tech', 'Others'];
        $selectedQualification = $d['qualification'] ?? '';
        $qualificationDetail = trim($d['qualification_detail'] ?? '');

        if (in_array($selectedQualification, $manualQualifications) && $qualificationDetail !== '') {
            $d['qualification'] = $selectedQualification . ' - ' . $qualificationDetail;
        }

        // Re-combine caste field
        if (($d['caste'] ?? '') === 'Others' && !empty($d['caste_detail'])) {
            $d['caste'] = 'Others - ' . trim($d['caste_detail']);
        }

        // Update in DB
        $stmt2 = $pdo->prepare(
            "UPDATE students SET
                batch_id = ?, course_name = ?, state = ?,
                reg_no = ?, aadhar = ?, boarding_lodging = ?, name = ?, father_or_husband_name = ?, gender = ?, dob = ?,
                qualification = ?, religion = ?, caste = ?, annual_income = ?, address = ?, village = ?, mandal = ?, district = ?,
                contact = ?, alt_contact = ?, batch_end = ?
            WHERE id = ? AND user_id = ?"
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
            $edit_id,
            $user_id
        ]);

        unset($_SESSION['student_form']);
        echo "<script>alert('Successfully updated!');location='student-details.php';</script>";
        exit;
    }
}

// Fetch projects for dropdown
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
                            <h1 class="m-0">Edit Student - Step <?= intval($step) ?></h1>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <!-- Card -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Multi-step Edit Form</h3>
                        </div>
                        <div class="card-body">
                            <!-- Progress indicator -->
                            <div class="progress mb-4" style="height: 10px;">
                                <?php $percent = ($step / 5) * 100; ?>
                                <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%;"
                                    aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <form method="post" id="regForm" data-edit-id="<?= $edit_id ?>">
                                <?php include "steps/step$step.php"; ?>

                                <div class="mt-3 d-flex justify-content-between">
                                    <div>
                                        <?php if ($step > 1): ?>
                                            <a href="?step=<?= intval($step - 1) ?>&edit=<?= $edit_id ?>" class="btn btn-secondary">
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
                            <i class="bi bi-check2-circle"></i> Confirm &amp; Update
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php include '../footer.php'; ?>
    </div>
    <script src="./js/form.js"></script>

    <script>
        // Pass both form data and existing data to JavaScript
        window.studentFormData = <?= json_encode($form) ?>;
        window.existingData = <?= json_encode($existing_data) ?>;
        console.log('Form data:', <?= json_encode($form) ?>);
        console.log('Existing data:', <?= json_encode($existing_data) ?>);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../js/adminlte.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
        integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
</body>

</html>