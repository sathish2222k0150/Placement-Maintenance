<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$student_id = $_GET['student_id'] ?? '';
$data = [];

if ($student_id) {
    $stmt = $pdo->prepare("SELECT * FROM placement_final_stage WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Placement Step 3</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Stylesheets -->
  <link rel="preload" href="../css/adminlte.css" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="../css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" crossorigin="anonymous" />
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper p-4">
      <div class="container-fluid">

        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Placement Details - Step 3</h3>
          </div>
          <form action="save-step3.php" method="POST">
            <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">

            <div class="card-body row g-3">
              <div class="col-md-4">
                <label for="date_of_joining" class="form-label">Date of Joining</label>
                <input type="date" class="form-control" name="date_of_joining" value="<?= htmlspecialchars($data['date_of_joining'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label for="designation" class="form-label">Designation</label>
                <input type="text" class="form-control" name="designation" value="<?= htmlspecialchars($data['designation'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label for="salary_per_month" class="form-label">Salary / Month</label>
                <input type="text" class="form-control" name="salary_per_month" value="<?= htmlspecialchars($data['salary_per_month'] ?? '') ?>" required>
              </div>

              <div class="col-md-6">
                <label for="other_perks" class="form-label">Other Perks</label>
                <textarea class="form-control" name="other_perks" rows="2"><?= htmlspecialchars($data['other_perks'] ?? '') ?></textarea>
              </div>

              <div class="col-md-6">
                <label for="organization_name" class="form-label">Organization Name</label>
                <input type="text" class="form-control" name="organization_name" value="<?= htmlspecialchars($data['organization_name'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($data['city'] ?? '') ?>" required>
              </div>

              <div class="col-md-8">
                <label for="organization_address" class="form-label">Organization Address</label>
                <textarea class="form-control" name="organization_address" rows="2" required><?= htmlspecialchars($data['organization_address'] ?? '') ?></textarea>
              </div>

              <div class="col-md-6">
                <label for="contact_person" class="form-label">Contact Person</label>
                <input type="text" class="form-control" name="contact_person" value="<?= htmlspecialchars($data['contact_person'] ?? '') ?>" required>
              </div>

              <div class="col-md-6">
                <label for="office_contact_number" class="form-label">Office Contact</label>
                <input type="text" class="form-control" name="office_contact_number" value="<?= htmlspecialchars($data['office_contact_number'] ?? '') ?>" required>
              </div>
            </div>

            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary">Submit Final</button>
            </div>
          </form>
        </div>

      </div>
    </div>

    <?php include '../footer.php'; ?>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
  <script src="../js/adminlte.js"></script>
</body>
</html>
