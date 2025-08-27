<?php
include '../config.php';
session_start();

$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    die("Student ID is required.");
}

// Fetch placement_initial
$stmt1 = $pdo->prepare("SELECT * FROM placement_initial WHERE student_id = ?");
$stmt1->execute([$student_id]);
$step1 = $stmt1->fetch(PDO::FETCH_ASSOC);

// Fetch placement_second_stage
$stmt2 = $pdo->prepare("SELECT * FROM placement_second_stage WHERE student_id = ?");
$stmt2->execute([$student_id]);
$step2 = $stmt2->fetch(PDO::FETCH_ASSOC);

// Fetch placement_final_stage
$stmt3 = $pdo->prepare("SELECT * FROM placement_final_stage WHERE student_id = ?");
$stmt3->execute([$student_id]);
$step3 = $stmt3->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Placement</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">

  <div class="container">
    <h2>Edit Placement Details</h2>
    <form action="update_placement.php" method="POST">
      <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">

      <!-- Step 1 -->
      <h4 class="text-primary mt-4">Step 1: Initial Placement</h4>
      <div class="mb-3">
        <label>Status</label>
        <select class="form-select" name="step1_status" required>
          <option value="Yes" <?= ($step1['status'] ?? '') === 'Yes' ? 'selected' : '' ?>>Yes</option>
          <option value="No" <?= ($step1['status'] ?? '') === 'No' ? 'selected' : '' ?>>No</option>
        </select>
      </div>
      <div class="mb-3">
        <label>Date of Joining</label>
        <input type="date" name="step1_date_of_joining" class="form-control" value="<?= $step1['date_of_joining'] ?? '' ?>">
      </div>
      <div class="mb-3">
        <label>Designation</label>
        <input type="text" name="step1_designation" class="form-control" value="<?= $step1['designation'] ?? '' ?>">
      </div>
      <div class="mb-3">
        <label>Salary Per Month</label>
        <input type="number" name="step1_salary_per_month" class="form-control" value="<?= $step1['salary_per_month'] ?? '' ?>">
      </div>
      <div class="mb-3">
        <label>Other Perks</label>
        <input type="text" name="step1_other_perks" class="form-control" value="<?= $step1['other_perks'] ?? '' ?>">
      </div>
      <div class="mb-3">
        <label>Organization Name</label>
        <input type="text" name="step1_organization_name" class="form-control" value="<?= $step1['organization_name'] ?? '' ?>">
      </div>
      <div class="mb-3">
        <label>City</label>
        <input type="text" name="step1_city" class="form-control" value="<?= $step1['city'] ?? '' ?>">
      </div>
      <div class="mb-3">
        <label>Organization Address</label>
        <textarea name="step1_organization_address" class="form-control"><?= $step1['organization_address'] ?? '' ?></textarea>
      </div>
      <div class="mb-3">
        <label>Contact Person</label>
        <input type="text" name="step1_contact_person" class="form-control" value="<?= $step1['contact_person'] ?? '' ?>">
      </div>
      <div class="mb-3">
        <label>Office Contact Number</label>
        <input type="text" name="step1_office_contact_number" class="form-control" value="<?= $step1['office_contact_number'] ?? '' ?>">
      </div>

      <!-- Step 2 (Only if status = No) -->
      <?php if (($step1['status'] ?? '') === 'No' || $step2): ?>
        <h4 class="text-success mt-4">Step 2: Second Stage (if No)</h4>
        <div class="mb-3">
          <label>Status</label>
          <select class="form-select" name="step2_status">
            <option value="Agreed" <?= ($step2['status'] ?? '') === 'Agreed' ? 'selected' : '' ?>>Agreed</option>
            <option value="Not Agreed" <?= ($step2['status'] ?? '') === 'Not Agreed' ? 'selected' : '' ?>>Not Agreed</option>
          </select>
        </div>
        <div class="mb-3">
          <label>Date of Joining</label>
          <input type="date" name="step2_date_of_joining" class="form-control" value="<?= $step2['date_of_joining'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Designation</label>
          <input type="text" name="step2_designation" class="form-control" value="<?= $step2['designation'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Salary Per Month</label>
          <input type="number" name="step2_salary_per_month" class="form-control" value="<?= $step2['salary_per_month'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Other Perks</label>
          <input type="text" name="step2_other_perks" class="form-control" value="<?= $step2['other_perks'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Organization Name</label>
          <input type="text" name="step2_organization_name" class="form-control" value="<?= $step2['organization_name'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>City</label>
          <input type="text" name="step2_city" class="form-control" value="<?= $step2['city'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Organization Address</label>
          <textarea name="step2_organization_address" class="form-control"><?= $step2['organization_address'] ?? '' ?></textarea>
        </div>
        <div class="mb-3">
          <label>Contact Person</label>
          <input type="text" name="step2_contact_person" class="form-control" value="<?= $step2['contact_person'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Office Contact Number</label>
          <input type="text" name="step2_office_contact_number" class="form-control" value="<?= $step2['office_contact_number'] ?? '' ?>">
        </div>
      <?php endif; ?>

      <!-- Step 3 (Only if second stage is Not Agreed) -->
      <?php if (($step2['status'] ?? '') === 'Not Agreed' || $step3): ?>
        <h4 class="text-warning mt-4">Step 3: Final Stage</h4>
        <div class="mb-3">
          <label>Date of Joining</label>
          <input type="date" name="step3_date_of_joining" class="form-control" value="<?= $step3['date_of_joining'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Designation</label>
          <input type="text" name="step3_designation" class="form-control" value="<?= $step3['designation'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Salary Per Month</label>
          <input type="number" name="step3_salary_per_month" class="form-control" value="<?= $step3['salary_per_month'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Other Perks</label>
          <input type="text" name="step3_other_perks" class="form-control" value="<?= $step3['other_perks'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Organization Name</label>
          <input type="text" name="step3_organization_name" class="form-control" value="<?= $step3['organization_name'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>City</label>
          <input type="text" name="step3_city" class="form-control" value="<?= $step3['city'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Organization Address</label>
          <textarea name="step3_organization_address" class="form-control"><?= $step3['organization_address'] ?? '' ?></textarea>
        </div>
        <div class="mb-3">
          <label>Contact Person</label>
          <input type="text" name="step3_contact_person" class="form-control" value="<?= $step3['contact_person'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label>Office Contact Number</label>
          <input type="text" name="step3_office_contact_number" class="form-control" value="<?= $step3['office_contact_number'] ?? '' ?>">
        </div>
      <?php endif; ?>

      <button type="submit" class="btn btn-success mt-3">Update Placement</button>
    </form>
  </div>
</body>
</html>
