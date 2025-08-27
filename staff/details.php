<?php
session_start();
require '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get logged-in user ID
$user_id = $_SESSION['user_id'];

// Initialize filter variables
$project_id = $_GET['project_id'] ?? '';
$batch_id = $_GET['batch_id'] ?? '';

// Base query
$query = "
    SELECT 
        s.*, 
        b.code AS batch_code, 
        b.start_date AS batch_start, 
        b.id AS batch_id,
        p.project AS project_name,
        pi.date_of_joining AS pi_joining, 
        pi.designation AS pi_designation, 
        pi.salary_per_month AS pi_salary,
        pi.other_perks AS pi_perks,
        pi.organization_name AS pi_org,
        pi.city AS pi_city,
        pi.organization_address AS pi_address,
        pi.contact_person AS pi_contact_person,
        pi.office_contact_number AS pi_office_contact,
        pi.status AS pi_status,
        pi.remarks AS pi_remarks,

        ps.date_of_joining AS ps_joining, 
        ps.designation AS ps_designation, 
        ps.salary_per_month AS ps_salary,
        ps.other_perks AS ps_perks,
        ps.organization_name AS ps_org,
        ps.city AS ps_city,
        ps.organization_address AS ps_address,
        ps.contact_person AS ps_contact_person,
        ps.office_contact_number AS ps_office_contact,
        ps.status AS ps_status,
        ps.remarks AS ps_remarks,

        pf.date_of_joining AS pf_joining, 
        pf.designation AS pf_designation, 
        pf.salary_per_month AS pf_salary,
        pf.other_perks AS pf_perks,
        pf.organization_name AS pf_org,
        pf.city AS pf_city,
        pf.organization_address AS pf_address,
        pf.contact_person AS pf_contact_person,
        pf.office_contact_number AS pf_office_contact

    FROM students s
    LEFT JOIN batches b ON s.batch_id = b.id
    LEFT JOIN projects p ON b.project_id = p.id
    LEFT JOIN placement_initial pi ON s.id = pi.student_id
    LEFT JOIN placement_second_stage ps ON s.id = ps.student_id
    LEFT JOIN placement_final_stage pf ON s.id = pf.student_id
";

// Apply filters
$where = ["s.user_id = ?"];
$params = [$user_id];

if (!empty($project_id)) {
    $where[] = "b.project_id = ?";
    $params[] = $project_id;
}

if (!empty($batch_id)) {
    $where[] = "s.batch_id = ?";
    $params[] = $batch_id;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY s.created_at DESC";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get projects for dropdown
$projects = $pdo->query("SELECT id, project FROM projects ORDER BY project")->fetchAll();

// Get batches based on selected project
$batches_query = "SELECT id, code FROM batches";
if (!empty($project_id)) {
    $batches_query .= " WHERE project_id = ?";
    $batches_stmt = $pdo->prepare($batches_query);
    $batches_stmt->execute([$project_id]);
} else {
    $batches_stmt = $pdo->query($batches_query);
}
$batches = $batches_stmt->fetchAll();

// Export to Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="placement_report_'.date('Y-m-d').'.xls"');

    $output = '<table border="1">
        <tr>
            <th colspan="27" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">Complete Student Placement Report</th>
        </tr>
        <tr>
            <th>S.No</th>
            <th>Project</th>
            <th>Course Name</th>
            <th>Batch Code</th>
            <th>State</th>
            <th>Batch Start</th>
            <th>Batch End</th>
            <th>Reg. No</th>
            <th>Aadhar No</th>
            <th>Boarding & Lodging</th>
            <th>Name</th>
            <th>Father / Husband Name</th>
            <th>Gender</th>
            <th>DOB</th>
            <th>Qualification</th>
            <th>Religion</th>
            <th>Caste</th>
            <th>Annual Income</th>
            <th>Address</th>
            <th>Village</th>
            <th>Mandal</th>
            <th>District</th>
            <th>Contact</th>
            <th>Alt. Contact</th>

            <!-- Placement Initial -->
            <th colspan="10" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">1st Discrepancy Report on interim Placement report by MIS</th>

            <!-- Placement Second -->
            <th colspan="10" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">2nd Discrepancy Report on interim Placement report by MIS</th>

            <!-- Placement Final -->
            <th colspan="9" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">Final Discrepancy Report by MIS</th>
        </tr>
        <tr>
            <th colspan="24"></th>
            <!-- Initial Placement Columns -->
            <th>Organization Name</th>
            <th>Organization Address</th>
            <th>Contact Person</th>
            <th>Contact Person Number</th>
            <th>Candidate Designation</th>
            <th>Salary</th>
            <th>Other Perks (if Any)</th>
            <th>Date of Joining</th>
            <th>Status of Employment</th>
            <th>Remarks</th>

            <!-- Second Stage -->
            <th>Organization Name</th>
            <th>Organization Address</th>
            <th>Contact Person</th>
            <th>Contact Person Number</th>
            <th>Candidate Designation</th>
            <th>Salary</th>
            <th>Other Perks (if Any)</th>
            <th>Date of Joining</th>
            <th>Status of Employment</th>
            <th>Remarks</th>

            <!-- Final Stage -->
            <th>Organization Name</th>
            <th>Organization Address</th>
            <th>Contact Person</th>
            <th>Contact Person Number</th>
            <th>Candidate Designation</th>
            <th>Salary</th>
            <th>Other Perks (if Any)</th>
            <th>Date of Joining</th>
        </tr>';

    if ($students) {
        $i = 1;
        foreach ($students as $row) {
            $output .= '<tr>
                <td>'.$i++.'</td>
                <td>'.htmlspecialchars($row['project_name']).'</td>
                <td>'.htmlspecialchars($row['course_name']).'</td>
                <td>'.htmlspecialchars($row['batch_code']).'</td>
                <td>'.htmlspecialchars($row['state']).'</td>
                <td>'.($row['batch_start'] ? date('d-m-Y', strtotime($row['batch_start'])) : '').'</td>
                <td>'.($row['batch_end'] ? date('d-m-Y', strtotime($row['batch_end'])) : '').'</td>
                <td>'.htmlspecialchars($row['reg_no']).'</td>
                <td>'.htmlspecialchars($row['aadhar']).'</td>
                <td>'.htmlspecialchars($row['boarding_lodging']).'</td>
                <td>'.htmlspecialchars($row['name']).'</td>
                <td>'.htmlspecialchars($row['father_or_husband_name']).'</td>
                <td>'.htmlspecialchars($row['gender']).'</td>
                <td>'.($row['dob'] ? date('d-m-Y', strtotime($row['dob'])) : '').'</td>
                <td>'.htmlspecialchars($row['qualification']).'</td>
                <td>'.htmlspecialchars($row['religion']).'</td>
                <td>'.htmlspecialchars($row['caste']).'</td>
                <td>'.htmlspecialchars($row['annual_income']).'</td>
                <td>'.htmlspecialchars($row['address']).'</td>
                <td>'.htmlspecialchars($row['village']).'</td>
                <td>'.htmlspecialchars($row['mandal']).'</td>
                <td>'.htmlspecialchars($row['district']).'</td>
                <td>'.htmlspecialchars($row['contact']).'</td>
                <td>'.htmlspecialchars($row['alt_contact']).'</td>

                <!-- Placement Initial -->
                <td>'.htmlspecialchars($row['pi_org']).'</td>
                <td>'.htmlspecialchars($row['pi_address']).'</td>
                <td>'.htmlspecialchars($row['pi_contact_person']).'</td>
                <td>'.htmlspecialchars($row['pi_office_contact']).'</td>
                <td>'.htmlspecialchars($row['pi_designation']).'</td>
                <td>'.htmlspecialchars($row['pi_salary']).'</td>
                <td>'.htmlspecialchars($row['pi_perks']).'</td>
                <td>'.($row['pi_joining'] ? date('d-m-Y', strtotime($row['pi_joining'])) : '').'</td>
                <td>'.htmlspecialchars($row['pi_status']).'</td>
                <td>'.htmlspecialchars($row['pi_remarks']).'</td>

                <!-- Placement Second -->
                <td>'.htmlspecialchars($row['ps_org']).'</td>
                <td>'.htmlspecialchars($row['ps_address']).'</td>
                <td>'.htmlspecialchars($row['ps_contact_person']).'</td>
                <td>'.htmlspecialchars($row['ps_office_contact']).'</td>
                <td>'.htmlspecialchars($row['ps_designation']).'</td>
                <td>'.htmlspecialchars($row['ps_salary']).'</td>
                <td>'.htmlspecialchars($row['ps_perks']).'</td>
                <td>'.($row['ps_joining'] ? date('d-m-Y', strtotime($row['ps_joining'])) : '').'</td>
                <td>'.htmlspecialchars($row['ps_status']).'</td>
                <td>'.htmlspecialchars($row['ps_remarks']).'</td>

                <!-- Placement Final -->
                <td>'.htmlspecialchars($row['pf_org']).'</td>
                <td>'.htmlspecialchars($row['pf_address']).'</td>
                <td>'.htmlspecialchars($row['pf_contact_person']).'</td>
                <td>'.htmlspecialchars($row['pf_office_contact']).'</td>
                <td>'.htmlspecialchars($row['pf_designation']).'</td>
                <td>'.htmlspecialchars($row['pf_salary']).'</td>
                <td>'.htmlspecialchars($row['pf_perks']).'</td>
                <td>'.($row['pf_joining'] ? date('d-m-Y', strtotime($row['pf_joining'])) : '').'</td>
            </tr>';
        }
    } else {
        $output .= '<tr><td colspan="63" style="text-align: center;">No records found.</td></tr>';
    }

    $output .= '</table>';
    echo $output;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Sharadha Skill Academy</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" integrity="sha384-Xx7C3X3tJ++jRw9G3DCtQPK9hLrFM9P7kD8m1YOyP5iDloMYg2mdy8ZNL7LZqh1I" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/adminlte.css" />
  <style>
    table {
      font-size: 13px;
      white-space: nowrap;
    }
    .wide-table {
      min-width: 120%;
    }
    .table-responsive {
      max-height: 90vh;
      overflow-x: auto;
    }
    th, td {
      vertical-align: middle !important;
    }
    .section-header {
      background-color: #f0f0f0;
      font-weight: bold;
      text-align: center;
    }
    .filter-box {
      background-color: #f8f9fa;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .export-btn-wrapper {
      margin-top: 15px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">
  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main class="app-main">
    <div class="wrapper">
      <div class="page-wrapper">
        <div class="container-xl">
          <div class="page-header d-print-none export-btn-wrapper">
            <div class="row align-items-center">
              <div class="col">
                <h2 class="page-title">Complete Student Placement Report</h2>
              </div>
              <div class="col-auto ms-auto">
                <a href="?export=excel&project_id=<?= $project_id ?>&batch_id=<?= $batch_id ?>" class="btn btn-primary">
                  <i class="bi bi-file-excel"></i> Export to Excel
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="page-body">
          <div class="container-xl">
            <div class="card">
              <div class="card-body">
                <div class="filter-box">
                  <form method="get" class="row g-3">
                    <div class="col-md-4">
                      <label for="project_id" class="form-label">Project</label>
                      <select class="form-select" id="project_id" name="project_id" onchange="this.form.submit()">
                        <option value="">All Projects</option>
                        <?php foreach ($projects as $project): ?>
                          <option value="<?= $project['id'] ?>" <?= ($project_id == $project['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($project['project']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="batch_id" class="form-label">Batch</label>
                      <select class="form-select" id="batch_id" name="batch_id" onchange="this.form.submit()">
                        <option value="">All Batches</option>
                        <?php foreach ($batches as $batch): ?>
                          <option value="<?= $batch['id'] ?>" <?= ($batch_id == $batch['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($batch['code']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                      <button type="submit" class="btn btn-primary me-2">Filter</button>
                      <a href="?" class="btn btn-outline-secondary">Reset</a>
                    </div>
                  </form>
                </div>

                <div class="table-responsive">
                  <table class="table table-bordered table-hover wide-table">
                    <thead class="table-light">
                      <tr>
                        <th>S.No</th>
                        <th>Project</th>
                        <th>Course Name</th>
                        <th>Batch Code</th>
                        <th>State</th>
                        <th>Batch Start</th>
                        <th>Batch End</th>
                        <th>Reg. No</th>
                        <th>Aadhar No</th>
                        <th>Boarding & Lodging</th>
                        <th>Name</th>
                        <th>Father / Husband Name</th>
                        <th>Gender</th>
                        <th>DOB</th>
                        <th>Qualification</th>
                        <th>Religion</th>
                        <th>Caste</th>
                        <th>Annual Income</th>
                        <th>Address</th>
                        <th>Village</th>
                        <th>Mandal</th>
                        <th>District</th>
                        <th>Contact</th>
                        <th>Alt. Contact</th>

                        <!-- Placement Initial -->
                        <th colspan="10" class="section-header">1st Discrepancy Report on interim Placement report by MIS</th>

                        <!-- Placement Second -->
                        <th colspan="10" class="section-header">2nd Discrepancy Report on interim Placement report by MIS</th>

                        <!-- Placement Final -->
                        <th colspan="9" class="section-header">Final Discrepancy Report by MIS</th>
                      </tr>
                      <tr>
                        <th colspan="24"></th>
                        <!-- Initial Stage -->
                        <th>Organization Name</th>
                        <th>Organization Address</th>
                        <th>Contact Person</th>
                        <th>Contact Person Number</th>
                        <th>Candidate Designation</th>
                        <th>Salary</th>
                        <th>Other Perks (if Any)</th>
                        <th>Date of Joining</th>
                        <th>Status of Employment</th>
                        <th>Remarks</th>

                        <!-- Second Stage -->
                        <th>Organization Name</th>
                        <th>Organization Address</th>
                        <th>Contact Person</th>
                        <th>Contact Person Number</th>
                        <th>Candidate Designation</th>
                        <th>Salary</th>
                        <th>Other Perks (if Any)</th>
                        <th>Date of Joining</th>
                        <th>Status of Employment</th>
                        <th>Remarks</th>

                        <!-- Final Stage -->
                        <th>Organization Name</th>
                        <th>Organization Address</th>
                        <th>Contact Person</th>
                        <th>Contact Person Number</th>
                        <th>Candidate Designation</th>
                        <th>Salary</th>
                        <th>Other Perks (if Any)</th>
                        <th>Date of Joining</th>

                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($students): 
                        $i = 1;
                        foreach ($students as $row): ?>
                        <tr>
                          <td><?= $i++ ?></td>
                          <td><?= htmlspecialchars($row['project_name']) ?></td>
                          <td><?= htmlspecialchars($row['course_name']) ?></td>
                          <td><?= htmlspecialchars($row['batch_code']) ?></td>
                          <td><?= htmlspecialchars($row['state']) ?></td>
                          <td><?= date('d-m-Y', strtotime($row['batch_start'])) ?></td>
                          <td><?= $row['batch_end'] ? date('d-m-Y', strtotime($row['batch_end'])) : '' ?></td>
                          <td><?= htmlspecialchars($row['reg_no']) ?></td>
                          <td><?= htmlspecialchars($row['aadhar']) ?></td>
                          <td><?= htmlspecialchars($row['boarding_lodging']) ?></td>
                          <td><?= htmlspecialchars($row['name']) ?></td>
                          <td><?= htmlspecialchars($row['father_or_husband_name']) ?></td>
                          <td><?= htmlspecialchars($row['gender']) ?></td>
                          <td><?= $row['dob'] ? date('d-m-Y', strtotime($row['dob'])) : '' ?></td>
                          <td><?= htmlspecialchars($row['qualification']) ?></td>
                          <td><?= htmlspecialchars($row['religion']) ?></td>
                          <td><?= htmlspecialchars($row['caste']) ?></td>
                          <td><?= htmlspecialchars($row['annual_income']) ?></td>
                          <td><?= htmlspecialchars($row['address']) ?></td>
                          <td><?= htmlspecialchars($row['village']) ?></td>
                          <td><?= htmlspecialchars($row['mandal']) ?></td>
                          <td><?= htmlspecialchars($row['district']) ?></td>
                          <td><?= htmlspecialchars($row['contact']) ?></td>
                          <td><?= htmlspecialchars($row['alt_contact']) ?></td>

                          <!-- Placement Initial -->
                          <td><?= htmlspecialchars($row['pi_org']) ?></td>
                          <td><?= htmlspecialchars($row['pi_address']) ?></td>
                          <td><?= htmlspecialchars($row['pi_contact_person']) ?></td>
                          <td><?= htmlspecialchars($row['pi_office_contact']) ?></td>
                          <td><?= htmlspecialchars($row['pi_designation']) ?></td>
                          <td><?= htmlspecialchars($row['pi_salary']) ?></td>
                          <td><?= htmlspecialchars($row['pi_perks']) ?></td>
                          <td><?= $row['pi_joining'] ? date('d-m-Y', strtotime($row['pi_joining'])) : '' ?></td>
                          <td><?= htmlspecialchars($row['pi_status']) ?></td>
                          <td><?= htmlspecialchars($row['pi_remarks']) ?></td>

                          <!-- Placement Second -->
                          <td><?= htmlspecialchars($row['ps_org']) ?></td>
                          <td><?= htmlspecialchars($row['ps_address']) ?></td>
                          <td><?= htmlspecialchars($row['ps_contact_person']) ?></td>
                          <td><?= htmlspecialchars($row['ps_office_contact']) ?></td>
                          <td><?= htmlspecialchars($row['ps_designation']) ?></td>
                          <td><?= htmlspecialchars($row['ps_salary']) ?></td>
                          <td><?= htmlspecialchars($row['ps_perks']) ?></td>
                          <td><?= $row['ps_joining'] ? date('d-m-Y', strtotime($row['ps_joining'])) : '' ?></td>
                          <td><?= htmlspecialchars($row['ps_status']) ?></td>
                          <td><?= htmlspecialchars($row['ps_remarks']) ?></td>

                          <!-- Placement Final -->
                          <td><?= htmlspecialchars($row['pf_org']) ?></td>
                          <td><?= htmlspecialchars($row['pf_address']) ?></td>
                          <td><?= htmlspecialchars($row['pf_contact_person']) ?></td>
                          <td><?= htmlspecialchars($row['pf_office_contact']) ?></td>
                          <td><?= htmlspecialchars($row['pf_designation']) ?></td>
                          <td><?= htmlspecialchars($row['pf_salary']) ?></td>
                          <td><?= htmlspecialchars($row['pf_perks']) ?></td>
                          <td><?= $row['pf_joining'] ? date('d-m-Y', strtotime($row['pf_joining'])) : '' ?></td>
                        </tr>
                      <?php endforeach; else: ?>
                        <tr><td colspan="63" class="text-center">No records found.</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include '../footer.php'; ?>
</div>

<script>
document.getElementById('project_id').addEventListener('change', function() {
    const projectId = this.value;
    const batchSelect = document.getElementById('batch_id');
    
    if (projectId) {
        fetch(`get-batches.php?project_id=${projectId}`)
            .then(response => response.json())
            .then(batches => {
                batchSelect.innerHTML = '<option value="">All Batches</option>';
                batches.forEach(batch => {
                    const option = document.createElement('option');
                    option.value = batch.id;
                    option.textContent = batch.code;
                    batchSelect.appendChild(option);
                });
            });
    } else {
        batchSelect.innerHTML = '<option value="">All Batches</option>';
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="../js/adminlte.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
</body>
</html>