<?php
include '../config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$currentMonth = date('Y-m');

// Total students assigned to this user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalStudents = $stmt->fetchColumn();

// Get current month batch IDs
$stmt = $pdo->prepare("SELECT id FROM batches WHERE DATE_FORMAT(start_date, '%Y-%m') = ?");
$stmt->execute([$currentMonth]);
$currentBatchIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

$currentBatchIdsString = implode(',', $currentBatchIds);
$currentBatchCount = 0;
$placementInitialCount = 0;
$placementFinalCount = 0;

if (!empty($currentBatchIds)) {
    // Students in current month batches
    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE batch_id IN ($currentBatchIdsString)");
    $currentBatchCount = $stmt->fetchColumn();

    // Students in placement_initial from current batch
    $stmt = $pdo->query("SELECT COUNT(DISTINCT pi.student_id)
                         FROM placement_initial pi
                         JOIN students s ON pi.student_id = s.id
                         WHERE s.batch_id IN ($currentBatchIdsString)");
    $placementInitialCount = $stmt->fetchColumn();

    // Students in placement_final_stage from current batch
    $stmt = $pdo->query("SELECT COUNT(DISTINCT pf.student_id)
                         FROM placement_final_stage pf
                         JOIN students s ON pf.student_id = s.id
                         WHERE s.batch_id IN ($currentBatchIdsString)");
    $placementFinalCount = $stmt->fetchColumn();
}
?>

<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Stats</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="staff-dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Stats</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="row">
        <!-- Total Students Assigned -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-primary">
            <div class="inner">
              <h3><?= $totalStudents ?></h3>
              <p>Total Students</p>
            </div>
            <!-- SVG icon -->
            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"></path>
            </svg>
            <a href="#" class="small-box-footer link-light">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>

        <!-- Students in Current Batch -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-success">
            <div class="inner">
              <h3><?= $currentBatchCount ?></h3>
              <p>Total Students Current Batch</p>
            </div>
            <!-- SVG icon -->
            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"></path>
            </svg>
            <a href="#" class="small-box-footer link-light">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>

        <!-- Placement Initial -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-warning">
            <div class="inner">
              <h3><?= $placementInitialCount ?></h3>
              <p>Placement Records</p>
            </div>
            <!-- SVG icon -->
            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"></path>
            </svg>
            <a href="#" class="small-box-footer link-dark">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>

        <!-- Final Discrepancy Report -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-danger">
            <div class="inner">
              <h3><?= $placementFinalCount ?></h3>
              <p>Final Discrepancy Report</p>
            </div>
            <!-- SVG icon -->
            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path clip-rule="evenodd" fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z"></path>
              <path clip-rule="evenodd" fill-rule="evenodd" d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z"></path>
            </svg>
            <a href="#" class="small-box-footer link-light">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>

