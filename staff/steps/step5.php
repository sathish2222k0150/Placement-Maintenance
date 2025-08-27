<div class="row">
  <div class="col-md-4 mb-3">
    <label class="form-label">Contact</label>
    <input name="contact" required maxlength="10" class="form-control contact-input"
      value="<?= htmlspecialchars($form['contact'] ?? '') ?>">
    <small class="text-danger contact-error" style="display:none">Must be exactly 10 digits</small>
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Alt Contact</label>
    <input name="alt_contact" maxlength="10" class="form-control alt-contact-input"
      value="<?= htmlspecialchars($form['alt_contact'] ?? '') ?>">
    <small class="text-danger alt-contact-error" style="display:none">Must be exactly 10 digits</small>
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Batch End Date</label>
    <input name="batch_end" type="date" class="form-control"
      value="<?= htmlspecialchars($form['batch_end'] ?? '') ?>">
  </div>
</div>