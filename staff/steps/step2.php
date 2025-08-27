<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Reg. No</label>
    <input name="reg_no" required class="form-control regno-input"
      value="<?= htmlspecialchars($form['reg_no'] ?? '') ?>">
    <small class="text-danger regno-error" style="display:none">Registration number is required</small>
    <small class="text-danger regno-exists-error" style="display:none">This registration number is already registered</small>
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">Aadhaar</label>
    <input name="aadhar" required maxlength="12" class="form-control aadhar-input" 
      value="<?= htmlspecialchars($form['aadhar'] ?? '') ?>">
    <small class="text-danger aadhar-error" style="display:none">Must be exactly 12 digits</small>
    <small class="text-danger aadhar-exists-error" style="display:none">This Aadhar number is already registered</small>
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">Boarding &amp; Lodging</label>
    <select name="boarding_lodging" class="form-select">
      <option <?= (($form['boarding_lodging'] ?? '') == 'Resident') ? 'selected' : '' ?>>Resident</option>
      <option <?= (($form['boarding_lodging'] ?? '') == 'Non-Resident') ? 'selected' : '' ?>>Non-Resident</option>
    </select>
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">Name</label>
    <input name="name" required class="form-control"
      value="<?= htmlspecialchars($form['name'] ?? '') ?>">
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label">Father/Husband Name</label>
    <input name="father_or_husband_name" required class="form-control"
      value="<?= htmlspecialchars($form['father_or_husband_name'] ?? '') ?>">
  </div>
</div>