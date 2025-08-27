<div class="row">
  <!-- Gender -->
  <div class="col-md-6 mb-3">
    <label class="form-label">Gender</label>
    <select name="gender" required class="form-select">
      <option value="">Select Gender</option>
      <option value="Male" <?= (($form['gender'] ?? '') == 'Male') ? 'selected' : '' ?>>Male</option>
      <option value="Female" <?= (($form['gender'] ?? '') == 'Female') ? 'selected' : '' ?>>Female</option>
      <option value="Transgender" <?= (($form['gender'] ?? '') == 'Transgender') ? 'selected' : '' ?>>Transgender</option>
    </select>
  </div>

  <!-- DOB -->
  <div class="col-md-6 mb-3">
    <label class="form-label">DOB</label>
    <input name="dob" type="date" required class="form-control"
      value="<?= htmlspecialchars($form['dob'] ?? '') ?>">
  </div>

  <!-- Qualification -->
  <?php
    $qualifications = ['8th Pass', 'SSLC', 'HSC', 'ITI', 'Diploma', 'Graduate', 'Post Graduate', 'B.E/B.Tech', 'Others'];
    $selectedQualification = '';
    foreach ($qualifications as $q) {
      if (isset($form['qualification']) && strpos($form['qualification'], $q) === 0) {
        $selectedQualification = $q;
        break;
      }
    }
  ?>
  <div class="col-md-6 mb-3">
    <label class="form-label">Qualification</label>
    <select name="qualification" id="qualification" required class="form-select">
      <option value="">Select Qualification</option>
      <?php foreach ($qualifications as $q): ?>
        <option value="<?= $q ?>" <?= ($selectedQualification === $q) ? 'selected' : '' ?>><?= $q ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Qualification Detail -->
  <div class="col-md-6 mb-3" id="qualification_detail_box" style="display: <?= in_array($selectedQualification, ['ITI','Diploma','Graduate','Post Graduate','B.E/B.Tech','Others']) ? 'block' : 'none' ?>;">
    <label class="form-label">Please specify</label>
    <input name="qualification_detail" id="qualification_detail" class="form-control"
      value="<?= htmlspecialchars($form['qualification_detail'] ?? '') ?>">
  </div>
  
  <!-- Religion -->
<?php
  $religions = ['Hindu', 'Muslim', 'Christian', 'Sikh', 'Buddhist', 'Jain', 'Other'];
  $selectedReligion = $form['religion'] ?? '';
?>
<div class="col-md-6 mb-3">
  <label class="form-label">Religion</label>
  <select name="religion" class="form-select" required>
    <option value="">Select Religion</option>
    <?php foreach ($religions as $r): ?>
      <option value="<?= $r ?>" <?= ($selectedReligion === $r) ? 'selected' : '' ?>><?= $r ?></option>
    <?php endforeach; ?>
  </select>
</div>
      
  <!-- Caste -->
  <?php
    $casteOptions = ['OC', 'OBC', 'BC', 'BC Muslims', 'MBC', 'SC', 'SCA', 'ST', 'Others'];
    $caste = $form['caste'] ?? '';
    $casteBase = strpos($caste, 'Others -') === 0 ? 'Others' : $caste;
    $casteDetailValue = strpos($caste, 'Others -') === 0 ? substr($caste, 9) : '';
  ?>
  <div class="col-md-6 mb-3">
    <label class="form-label">Caste</label>
    <select name="caste" required class="form-select" id="casteSelect">
      <option value="">-- Select --</option>
      <?php foreach ($casteOptions as $c): ?>
        <option value="<?= $c ?>" <?= ($casteBase === $c) ? 'selected' : '' ?>><?= $c ?></option>
      <?php endforeach; ?>
    </select>
    <input type="text" name="caste_detail" id="casteDetail" class="form-control mt-2"
      placeholder="Please specify caste"
      value="<?= (strpos($form['caste'] ?? '', 'Others -') === 0) ? htmlspecialchars(substr($form['caste'], 9)) : '' ?>"
      style="<?= (strpos($form['caste'] ?? '', 'Others -') === 0) ? '' : 'display:none;' ?>">
  </div>

  <!-- Annual Income -->
  <div class="col-md-6 mb-3">
    <label class="form-label">Annual Income</label>
    <select name="annual_income" required class="form-select">
      <option value="">Select Annual Income</option>
      <option value="75000 - 100000" <?= (($form['annual_income'] ?? '') == '75000 - 100000') ? 'selected' : '' ?>>75000 - 100000</option>
      <option value="100000 - 200000" <?= (($form['annual_income'] ?? '') == '100000 - 200000') ? 'selected' : '' ?>>100000 - 200000</option>
      <option value="200000 - 300000" <?= (($form['annual_income'] ?? '') == '200000 - 300000') ? 'selected' : '' ?>>200000 - 300000</option>
      <option value="above 300000" <?= (($form['annual_income'] ?? '') == 'above 300000') ? 'selected' : '' ?>>Above 300000</option>
    </select>
  </div>
</div>

<!-- JS to toggle qualification and caste detail fields -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const qualificationSelect = document.getElementById('qualification');
  const qualificationDetailBox = document.getElementById('qualification_detail_box');
  const qualificationDetailInput = document.getElementById('qualification_detail');
  const manualQualifications = ["ITI", "Diploma", "Graduate", "Post Graduate", "B.E/B.Tech", "Others"];

  function toggleQualificationDetail() {
    if (manualQualifications.includes(qualificationSelect.value)) {
      qualificationDetailBox.style.display = 'block';
    } else {
      qualificationDetailBox.style.display = 'none';
      qualificationDetailInput.value = '';
    }
  }

  setTimeout(toggleQualificationDetail, 50); // Ensure it runs after value is set
  qualificationSelect.addEventListener('change', toggleQualificationDetail);

  const casteSelect = document.getElementById('casteSelect');
  const casteDetailInput = document.getElementById('casteDetail');

  function toggleCasteDetail() {
    if (casteSelect.value === 'Others') {
      casteDetailInput.style.display = 'block';
      casteDetailInput.required = true;
    } else {
      casteDetailInput.style.display = 'none';
      casteDetailInput.required = false;
      casteDetailInput.value = '';
    }
  }

  setTimeout(toggleCasteDetail, 50);
  casteSelect.addEventListener('change', toggleCasteDetail);
});
</script>
