<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$student_name = '';
$student_id = $_GET['student_id'] ?? '';
$placementData = [];

if ($student_id) {
    $stmt = $pdo->prepare("SELECT name FROM students WHERE id = ? AND user_id = ?");
    $stmt->execute([$student_id, $_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $student_name = $student['name'];
        $placement_stmt = $pdo->prepare("SELECT * FROM placement_initial WHERE student_id = ?");
        $placement_stmt->execute([$student_id]);
        $placementData = $placement_stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Placement Step 1</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link rel="preload" href="../css/adminlte.css" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="../css/adminlte.css" />
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">
  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main class="content-wrapper p-4">
    <div class="container-fluid">
      <?php if ($student_name): ?>
        <div class="mb-3">
          <h4 class="text-muted">Student: <?= htmlspecialchars($student_name) ?></h4>
        </div>

        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Placement Details - Step 1</h5>
          </div>
          <form id="step1Form" method="POST">
            <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">

            <div class="card-body row g-3">
              <div class="col-md-4">
                <label for="date_of_joining" class="form-label">Date of Joining</label>
                <input type="date" class="form-control" name="date_of_joining" value="<?= htmlspecialchars($placementData['date_of_joining'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label for="designation" class="form-label">Designation</label>
                <input type="text" class="form-control" name="designation" value="<?= htmlspecialchars($placementData['designation'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label for="salary_per_month" class="form-label">Salary / Month</label>
                <input type="text" class="form-control" name="salary_per_month" value="<?= htmlspecialchars($placementData['salary_per_month'] ?? '') ?>" required>
              </div>

              <div class="col-md-6">
                <label for="other_perks" class="form-label">Other Perks</label>
                <textarea class="form-control" name="other_perks" rows="2"><?= htmlspecialchars($placementData['other_perks'] ?? '') ?></textarea>
              </div>

              <div class="col-md-6">
                <label for="organization_name" class="form-label">Organization Name</label>
                <input type="text" class="form-control" name="organization_name" value="<?= htmlspecialchars($placementData['organization_name'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($placementData['city'] ?? '') ?>" required>
              </div>

              <div class="col-md-8">
                <label for="organization_address" class="form-label">Organization Address</label>
                <textarea class="form-control" name="organization_address" rows="2" required><?= htmlspecialchars($placementData['organization_address'] ?? '') ?></textarea>
              </div>

              <div class="col-md-6">
                <label for="contact_person" class="form-label">Contact Person</label>
                <input type="text" class="form-control" name="contact_person" value="<?= htmlspecialchars($placementData['contact_person'] ?? '') ?>" required>
              </div>

              <div class="col-md-6">
                <label for="office_contact_number" class="form-label">Office Contact</label>
                <input type="text" class="form-control" name="office_contact_number" value="<?= htmlspecialchars($placementData['office_contact_number'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label for="status" class="form-label">Placement Status</label>
                <select class="form-select" name="status" required>
                  <option value="">-- Select Status --</option>
                  <option value="Yes" <?= ($placementData['status'] ?? '') === 'Yes' ? 'selected' : '' ?>>Yes</option>
                  <option value="No" <?= ($placementData['status'] ?? '') === 'No' ? 'selected' : '' ?>>No</option>
                </select>
              </div>
              <div class="col-md-12" id="remarks-field" style="display: none;">
                <label for="remarks" class="form-label">Remarks (if status is No)</label>
                <textarea class="form-control" name="remarks" rows="3"><?= htmlspecialchars($placementData['remarks'] ?? '') ?></textarea>
              </div>
            </div>

            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary">Next</button>
            </div>
          </form>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">Student not found or not assigned to you.</div>
      <?php endif; ?>
    </div>
  </main>

  <?php include '../footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
<script src="../js/adminlte.js"></script>

<script>
  document.getElementById("step1Form").addEventListener("submit", function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);

    fetch('save-step1.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (!response.ok) throw new Error("Save failed");
      const status = formData.get('status');
      const studentId = formData.get('student_id');
      const target = (status === 'Yes') ? 'preview.php' : 'edit-step-2.php';
      window.location.href = `${target}?student_id=${encodeURIComponent(studentId)}`;
    })
    .catch(error => {
      alert("There was an error saving the form. Please try again.");
      console.error(error);
    });
  });
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const statusSelect = document.querySelector('select[name="status"]');
  const remarksField = document.getElementById('remarks-field');

  function toggleRemarks() {
    if (statusSelect.value === "No") {
      remarksField.style.display = "block";
    } else {
      remarksField.style.display = "none";
    }
  }

  statusSelect.addEventListener("change", toggleRemarks);
  toggleRemarks(); // Trigger on load if editing existing data
});
</script>

</body>
</html>
