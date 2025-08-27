<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

$rows = $_SESSION['preview_data'] ?? [];
$user_id = $_SESSION['user_id'] ?? null;
$project_id = $_SESSION['project_id'] ?? null;
$batch_id = $_SESSION['batch_id'] ?? null;

if (!$rows || !$user_id || !$batch_id) {
    header("Location: upload-students.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview Student Data</title>

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
    .sticky-header {
      position: sticky;
      top: 0;
      background: white;
      z-index: 100;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="container mt-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h4 class="mb-0">Preview Student Data</h4>
        </div>
        <div class="card-body">
          <h3>📋 Preview Data</h3>
          <p>Found <?= count($rows) - 1 ?> data rows.</p>
          <form method="POST" action="upload-students.php">
            <div class="preview-container border rounded p-2 mb-3">
              <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                  <?php if (!empty($rows[0])): ?>
                    <thead class="table-light sticky-header">
                      <tr>
                        <th>S.No</th>
                        <?php foreach ($rows[0] as $header): ?>
                          <th><?= htmlspecialchars($header ?? '') ?></th>
                        <?php endforeach; ?>
                      </tr>
                    </thead>
                  <?php endif; ?>
                  <tbody>
                    <?php for ($i = 1; $i < count($rows); $i++): ?>
                      <tr>
                        <td><?= $i ?></td>
                        <?php foreach ($rows[$i] as $cell): ?>
                          <td><?= htmlspecialchars($cell ?? '') ?></td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endfor; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <button type="submit" name="confirm" class="btn btn-success btn-lg">
              <i class="bi bi-check-circle-fill me-1"></i> Confirm & Insert
            </button>
            <a href="upload-students.php" class="btn btn-outline-secondary btn-lg ms-2">
              <i class="bi bi-x-circle-fill me-1"></i> Cancel
            </a>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="./js/adminlte.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
</body>
</html>