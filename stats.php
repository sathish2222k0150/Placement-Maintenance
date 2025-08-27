<?php
include 'config.php';

// Get total stats
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$placementInitialCount = $pdo->query("SELECT COUNT(*) FROM placement_initial")->fetchColumn();
$placementFinalCount = $pdo->query("SELECT COUNT(*) FROM placement_final_stage")->fetchColumn();
$projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$batchCount = $pdo->query("SELECT COUNT(*) FROM batches")->fetchColumn();

// Batches started in current month
$currentMonth = date('m');
$currentYear = date('Y');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM batches WHERE MONTH(start_date) = :month AND YEAR(start_date) = :year");
$stmt->execute([':month' => $currentMonth, ':year' => $currentYear]);
$currentBatchCount = $stmt->fetchColumn();

// Staff users
$staffCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn();
?>


<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid py-3">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Stats</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Stats</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Stat Boxes -->
  <div class="app-content">
    <div class="container-fluid">
      <div class="row">
        <!-- Total Students -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-primary">
            <div class="inner">
              <h3><?= $totalStudents ?></h3>
              <p>Total Students</p>
            </div>
            <a href="details.php" class="small-box-footer link-light">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>

        <!-- Current Month's Batches -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-success">
            <div class="inner">
              <h3><?= $currentBatchCount ?></h3>
              <p>Current Month's Batches</p>
            </div>
            <a href="show-batches.php" class="small-box-footer link-light">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>

        <!-- Placement Initial -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-warning">
            <div class="inner">
              <h3><?= $placementInitialCount ?></h3>
              <p>Placement Records</p>
            </div>
            <a href="#" class="small-box-footer link-dark">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>

        <!-- Total Projects -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-primary">
            <div class="inner">
              <h3><?= $projectCount ?></h3>
              <p>Total Projects</p>
            </div>
            <a href="show-project.php" class="small-box-footer link-light">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>

        <!-- Total Batches -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-success">
            <div class="inner">
              <h3><?= $batchCount ?></h3>
              <p>Total Batches</p>
            </div>
            <a href="show-batches.php" class="small-box-footer link-light">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>

        <!-- Total Staff Users -->
        <div class="col-lg-3 col-6">
          <div class="small-box text-bg-warning">
            <div class="inner">
              <h3><?= $staffCount ?></h3>
              <p>Total Staff Users</p>
            </div>
            <a href="user-details.php" class="small-box-footer link-dark">More info <i class="bi bi-link-45deg"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Section - Moved Below Stat Boxes -->
  <!-- Full Width Line Chart -->
<div class="container-fluid mt-4">
  <div class="row">
    <div class="col-12">
      <canvas id="lineChart" style="width: 100%; height: 400px;"></canvas>
    </div>
  </div>
</div>

<script>
const lineCtx = document.getElementById('lineChart').getContext('2d');

const statsData = {
  labels: ['Students', 'Placement First', 'Placement Final', 'Projects', 'Total Batches', 'This Month Batches', 'Staff'],
  values: [<?= $totalStudents ?>, <?= $placementInitialCount ?>, <?= $placementFinalCount ?>, <?= $projectCount ?>, <?= $batchCount ?>, <?= $currentBatchCount ?>, <?= $staffCount ?>]
};

// Line Chart (wave style)
new Chart(lineCtx, {
  type: 'line',
  data: {
    labels: statsData.labels,
    datasets: [{
      label: 'Statistics',
      data: statsData.values,
      fill: true,
      tension: 0.4, // smooth wave effect
      backgroundColor: 'rgba(54, 162, 235, 0.2)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 2,
      pointBackgroundColor: 'rgba(54, 162, 235, 1)'
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 }
      }
    }
  }
});
</script>


