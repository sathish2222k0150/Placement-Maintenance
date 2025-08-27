<?php
include '../config.php';
session_start();

$step1 = $_SESSION['step1'] ?? null;
$step2 = $_SESSION['step2'] ?? null;
$step3 = $_SESSION['step3'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Sharadha Skill Academy</title>
  <link rel="preload" href="../css/adminlte.css" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="../css/adminlte.css" />
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper p-4">
      <div class="container">

        <!-- Alerts from Session -->
        <?php if (isset($_SESSION['placement_success'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['placement_success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php unset($_SESSION['placement_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['placement_error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['placement_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php unset($_SESSION['placement_error']); ?>
        <?php endif; ?>

        <!-- Trigger Modal Button -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#previewModal">
          Show Preview
        </button>

        <!-- Modal -->
        <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview Placement Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body">
                <?php if ($step1): ?>
                  <h5 class="text-primary">Step 1</h5>
                  <ul class="list-group mb-3">
                    <?php foreach ($step1 as $key => $val): ?>
                      <li class="list-group-item">
                        <strong><?= ucfirst(str_replace('_', ' ', htmlspecialchars($key))) ?>:</strong>
                        <?= htmlspecialchars($val) ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <?php if ($step2): ?>
                  <h5 class="text-success">Step 2</h5>
                  <ul class="list-group mb-3">
                    <?php foreach ($step2 as $key => $val): ?>
                      <li class="list-group-item">
                        <strong><?= ucfirst(str_replace('_', ' ', htmlspecialchars($key))) ?>:</strong>
                        <?= htmlspecialchars($val) ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <?php if ($step3): ?>
                  <h5 class="text-warning">Step 3</h5>
                  <ul class="list-group mb-3">
                    <?php foreach ($step3 as $key => $val): ?>
                      <li class="list-group-item">
                        <strong><?= ucfirst(str_replace('_', ' ', htmlspecialchars($key))) ?>:</strong>
                        <?= htmlspecialchars($val) ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>

              <div class="modal-footer">
                <form id="placementForm">
                  <button type="submit" class="btn btn-success">Submit All</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <?php include '../footer.php'; ?>
  </div>

  <!-- Bootstrap + AdminLTE Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="../js/adminlte.js"></script>

  <!-- AJAX Form Submission -->
  <script>
    document.getElementById('placementForm').addEventListener('submit', function(e) {
      e.preventDefault();

      fetch('submit.php', {
        method: 'POST'
      })
      .then(response => response.text())
      .then(data => {
        const alertContainer = document.createElement('div');
        alertContainer.className = 'alert alert-success alert-dismissible fade show mt-3';
        alertContainer.innerHTML = data + `
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        document.querySelector('.container').prepend(alertContainer);

        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('previewModal'));
        if (modal) modal.hide();
      })
      .catch(error => {
        const alertContainer = document.createElement('div');
        alertContainer.className = 'alert alert-danger alert-dismissible fade show mt-3';
        alertContainer.innerHTML = "An error occurred: " + error + `
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('.container').prepend(alertContainer);
      });
    });
  </script>
</body>

</html>
