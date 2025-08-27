<?php
$form = $_SESSION['student_form'] ?? [];
$selected_project = $form['project_id'] ?? '';
$selected_batch = $form['batch_id'] ?? '';
$batch_start = $form['batch_start'] ?? '';
?>
<div class="row">
  <!-- Project Dropdown -->
  <div class="col-md-6 mb-3">
    <label class="form-label">Project</label>
    <select name="project_id" id="projectDropdown" class="form-control" required>
      <option value="">Select Project</option>
      <?php foreach ($projects as $proj): ?>
        <option value="<?= $proj['id'] ?>" <?= $proj['id'] == $selected_project ? 'selected' : '' ?>>
          <?= htmlspecialchars($proj['project']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Batch Dropdown -->
  <div class="col-md-6 mb-3">
    <label class="form-label">Centre &amp; Batch No</label>
    <select name="batch_id" id="batchDropdown" class="form-control" required>
      <option value="">Select Batch</option>
      <!-- Filled by JavaScript or optionally here -->
    </select>
  </div>

  <?php
    $indian_states = [
      'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
      'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka',
      'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram',
      'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu',
      'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
      'Andaman and Nicobar Islands', 'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu',
      'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry'
    ];
    $selected_state = $_SESSION['student_form']['state'] ?? 'Tamil Nadu';
    ?>
    <div class="col-md-6 mb-3">
      <label class="form-label">State</label>
      <select name="state" class="form-select" required>
        <option value="">-- Select State --</option>
        <?php foreach ($indian_states as $state): ?>
          <option value="<?= htmlspecialchars($state) ?>" <?= $selected_state === $state ? 'selected' : '' ?>>
            <?= htmlspecialchars($state) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>


  <!-- Batch Start Date -->
  <div class="col-md-6 mb-3">
    <label class="form-label">Batch Start Date</label>
    <input name="batch_start" id="batchStartDate" type="date" class="form-control" value="<?= htmlspecialchars($batch_start) ?>" readonly>
  </div>
</div>
