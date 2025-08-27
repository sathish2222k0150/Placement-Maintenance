<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check for success/error messages from previous operation
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

function formatDate($dateValue) {
    if (empty($dateValue)) return null;
    if (is_numeric($dateValue)) {
        try {
            if ($dateValue > 1) {
                return Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
    $timestamp = strtotime($dateValue);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

// Fetch users, projects, batches for form dropdowns
try {
    $users = $pdo->query("SELECT id, full_name FROM users")->fetchAll(PDO::FETCH_ASSOC);
    $projects = $pdo->query("SELECT id, project FROM projects")->fetchAll(PDO::FETCH_ASSOC);
    $batches = $pdo->query("SELECT id, code FROM batches")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error fetching form data: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preview'])) {
    if (isset($_FILES['excel']) && $_FILES['excel']['error'] === UPLOAD_ERR_OK) {
        $user_id = $_POST['user_id'];
        $project_id = $_POST['project_id'];
        $batch_id = $_POST['batch_id'];

        try {
            // Read Excel in memory-efficient way
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($_FILES['excel']['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();

            $rows = [];
            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                // Skip empty rows
                if (count(array_filter($rowData, fn($v) => $v !== null && $v !== '')) === 0) continue;
                $rows[] = $rowData;
            }

            if (count($rows) <= 1) {
                $_SESSION['error_message'] = "❌ The uploaded file is empty or contains only a header row.";
                header("Location: upload-students.php");
                exit;
            }

            $_SESSION['preview_data'] = $rows;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['project_id'] = $project_id;
            $_SESSION['batch_id'] = $batch_id;

            header("Location: preview.php");
            exit;

        } catch (Exception $e) {
            $_SESSION['error_message'] = "❌ Error processing file: " . $e->getMessage();
            header("Location: upload-students.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "❌ File upload failed.";
        header("Location: upload-students.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $rows = $_SESSION['preview_data'] ?? [];
    $user_id = $_SESSION['user_id'] ?? null;
    $batch_id = $_SESSION['batch_id'] ?? null;

    if (!$rows || !$user_id || !$batch_id) {
        $_SESSION['error_message'] = "❌ Session data missing.";
        header("Location: upload-students.php");
        exit;
    }

    $headers = array_map('strtolower', $rows[0]);
    $getIndex = fn($name) => array_search(strtolower($name), $headers);

    $pdo->beginTransaction();
    try {
        foreach (array_slice($rows, 1) as $row) {
            $initial_doj = $row[$getIndex('date of joining initial')] ?? null;
            $second_doj  = $row[$getIndex('date of joining second')] ?? null;

            // Student data fields
            $state                  = $row[0] ?? null;
            $reg_no                 = $row[1] ?? null;
            $aadhar                 = $row[2] ?? null;
            $boarding_lodging       = $row[3] ?? null;
            $course_name            = $row[4] ?? null;
            $name                   = $row[5] ?? null;
            $father_or_husband_name = $row[6] ?? null;
            $gender                 = $row[7] ?? null;
            $dob                    = $row[8] ?? null;
            $qualification          = $row[9] ?? null;
            $caste                  = $row[10] ?? null;
            $annual_income          = $row[11] ?? null;
            $address                = $row[12] ?? null;
            $village                = $row[13] ?? null;
            $mandal                 = $row[14] ?? null;
            $district               = $row[15] ?? null;
            $contact                = $row[16] ?? null;
            $alt_contact            = $row[17] ?? null;
            $batch_end              = $row[18] ?? null;

            // Initial placement data fields
            $initial_designation    = $row[20] ?? null;
            $initial_salary         = $row[21] ?? null;
            $initial_perks          = $row[22] ?? null;
            $initial_org_name       = $row[23] ?? null;
            $initial_city           = $row[24] ?? null;
            $initial_org_address    = $row[25] ?? null;
            $initial_contact_person = $row[26] ?? null;
            $initial_office_contact = $row[27] ?? null;
            $initial_status         = isset($row[28]) ? trim($row[28]) : null;
            $initial_remarks        = $row[29] ?? null;

            // Second placement
            $second_designation     = $row[30] ?? null;
            $second_salary          = $row[31] ?? null;
            $second_perks           = $row[32] ?? null;
            $second_org_name        = $row[33] ?? null;
            $second_city            = $row[34] ?? null;
            $second_org_address     = $row[35] ?? null;
            $second_contact_person  = $row[36] ?? null;
            $second_office_contact  = $row[37] ?? null;
            $second_status          = isset($row[38]) ? trim($row[38]) : null;

            // Final placement
            $final_doj              = $row[39] ?? null;
            $final_designation      = $row[40] ?? null;
            $final_salary           = $row[41] ?? null;
            $final_perks            = $row[42] ?? null;
            $final_org_name         = $row[43] ?? null;
            $final_city             = $row[44] ?? null;
            $final_org_address      = $row[45] ?? null;
            $final_contact_person   = $row[46] ?? null;
            $final_office_contact   = $row[47] ?? null;

            if (strtolower($initial_status) === 'no') $initial_remarks = null;
            // Trim spaces and remove any non-digit characters
                $aadhar = preg_replace('/\D/', '', $aadhar);

                // Optionally truncate if longer than 12 digits
                $aadhar = substr($aadhar, 0, 12);

                // Or validate strictly
                if (strlen($aadhar) !== 12) {
                    throw new Exception("Invalid Aadhar number: $aadhar");
                }

            // Insert student
            $stmtStudent = $pdo->prepare("
                INSERT INTO students 
                (user_id, batch_id, course_name, state, reg_no, aadhar, boarding_lodging, name, father_or_husband_name, gender, dob, qualification, religion, caste, annual_income, address, village, mandal, district, contact, alt_contact, batch_end) 
                VALUES 
                (:user_id, :batch_id, :course_name, :state, :reg_no, :aadhar, :boarding_lodging, :name, :father_or_husband_name, :gender, :dob, :qualification, 'N/A', :caste, :annual_income, :address, :village, :mandal, :district, :contact, :alt_contact, :batch_end)
            ");
            $stmtStudent->execute([
                ':user_id'=>$user_id, ':batch_id'=>$batch_id, ':course_name'=>$course_name, ':state'=>$state,
                ':reg_no'=>$reg_no, ':aadhar'=>$aadhar, ':boarding_lodging'=>$boarding_lodging, ':name'=>$name,
                ':father_or_husband_name'=>$father_or_husband_name, ':gender'=>$gender, ':dob'=>formatDate($dob),
                ':qualification'=>$qualification, ':caste'=>$caste, ':annual_income'=>$annual_income, ':address'=>$address,
                ':village'=>$village, ':mandal'=>$mandal, ':district'=>$district, ':contact'=>$contact, ':alt_contact'=>$alt_contact,
                ':batch_end'=>formatDate($batch_end)
            ]);
            $student_id = $pdo->lastInsertId();

            // Insert initial placement
            $stmtInitial = $pdo->prepare("
                INSERT INTO placement_initial 
                (student_id, date_of_joining, designation, salary_per_month, other_perks, organization_name, city, organization_address, contact_person, office_contact_number, status, remarks) 
                VALUES 
                (:student_id, :date_of_joining, :designation, :salary, :perks, :org_name, :city, :org_address, :contact_person, :office_contact, :status, :remarks)
            ");
            $stmtInitial->execute([
                ':student_id'=>$student_id, ':date_of_joining'=>formatDate($initial_doj),
                ':designation'=>$initial_designation, ':salary'=>$initial_salary, ':perks'=>$initial_perks,
                ':org_name'=>$initial_org_name, ':city'=>$initial_city, ':org_address'=>$initial_org_address,
                ':contact_person'=>$initial_contact_person, ':office_contact'=>$initial_office_contact,
                ':status'=>$initial_status, ':remarks'=>$initial_remarks
            ]);
            $placement_initial_id = $pdo->lastInsertId();

            // Second stage
            if (!empty($second_doj) || !empty($second_designation)) {
                $stmtSecond = $pdo->prepare("
                    INSERT INTO placement_second_stage 
                    (student_id, placement_initial_id, date_of_joining, designation, salary_per_month, other_perks, organization_name, city, organization_address, contact_person, office_contact_number, status, remarks) 
                    VALUES 
                    (:student_id, :placement_initial_id, :date_of_joining, :designation, :salary, :perks, :org_name, :city, :org_address, :contact_person, :office_contact, :status, :remarks)
                ");
                $stmtSecond->execute([
                    ':student_id'=>$student_id, ':placement_initial_id'=>$placement_initial_id, ':date_of_joining'=>formatDate($second_doj),
                    ':designation'=>$second_designation, ':salary'=>$second_salary, ':perks'=>$second_perks,
                    ':org_name'=>$second_org_name, ':city'=>$second_city, ':org_address'=>$second_org_address,
                    ':contact_person'=>$second_contact_person, ':office_contact'=>$second_office_contact,
                    ':status'=>$second_status, ':remarks'=>null
                ]);
                $placement_second_stage_id = $pdo->lastInsertId();

                // Final stage
                if (strtolower($second_status)==='not agreed' && (!empty($final_doj) || !empty($final_designation))) {
                    $stmtFinal = $pdo->prepare("
                        INSERT INTO placement_final_stage
                        (student_id, placement_second_stage_id, date_of_joining, designation, salary_per_month, other_perks, organization_name, city, organization_address, contact_person, office_contact_number)
                        VALUES
                        (:student_id, :placement_second_stage_id, :date_of_joining, :designation, :salary, :perks, :org_name, :city, :org_address, :contact_person, :office_contact)
                    ");
                    $stmtFinal->execute([
                        ':student_id'=>$student_id, ':placement_second_stage_id'=>$placement_second_stage_id,
                        ':date_of_joining'=>formatDate($final_doj), ':designation'=>$final_designation, ':salary'=>$final_salary,
                        ':perks'=>$final_perks, ':org_name'=>$final_org_name, ':city'=>$final_city, ':org_address'=>$final_org_address,
                        ':contact_person'=>$final_contact_person, ':office_contact'=>$final_office_contact
                    ]);
                }
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = "✅ All " . (count($rows) - 1) . " records processed and inserted successfully!";
        header("Location: upload-students.php");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "❌ Database Error: " . $e->getMessage() . ". No records were inserted. The transaction has been rolled back.";
        header("Location: upload-students.php");
        exit;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Upload Student Data</title>

  <link rel="preload" href="./css/adminlte.css" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="./css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .preview-container {
      max-height: 70vh;
      overflow-y: auto;
    }
    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1100;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="container mt-4">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="mb-0">Upload Student Data</h4>
          <a href="sample_student_data.xlsx" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Sample
          </a>
        </div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="user_id" class="form-label">User:</label>
              <select id="user_id" name="user_id" class="form-select" required>
                <option value="">-- Select User --</option>
                <?php foreach ($users as $u): ?>
                  <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="project_id" class="form-label">Project:</label>
              <select id="project_id" name="project_id" class="form-select" required>
                <option value="">-- Select Project --</option>
                <?php foreach ($projects as $p): ?>
                  <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['project']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="batch_id" class="form-label">Batch:</label>
              <select id="batch_id" name="batch_id" class="form-select" required>
                <option value="">-- Select Batch --</option>
                <?php foreach ($batches as $b): ?>
                  <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['code']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="excel" class="form-label">Excel File (.xlsx):</label>
              <input type="file" id="excel" name="excel" class="form-control" accept=".xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            </div>

            <button type="submit" name="preview" class="btn btn-primary">
              <i class="bi bi-eye-fill me-1"></i> Preview & Upload
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div class="toast-container">
    <?php if ($success_message): ?>
      <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
        <div class="toast-header bg-success text-white">
          <strong class="me-auto">Success</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          <?= $success_message ?>
        </div>
      </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
      <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
        <div class="toast-header bg-danger text-white">
          <strong class="me-auto">Error</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          <?= $error_message ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="./js/adminlte.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize Bootstrap toasts
      var toastElList = [].slice.call(document.querySelectorAll('.toast'));
      var toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl, {
          autohide: true,
          delay: 5000
        });
      });
      
      // Show toasts
      toastList.forEach(function(toast) {
        toast.show();
      });
    });
  </script>
</body>
</html>