<?php
include '../config.php';

// Fetch all batches with project names
try {
  $stmt = $pdo->prepare("
    SELECT b.*, p.project AS project_name
    FROM batches b
    LEFT JOIN projects p ON b.project_id = p.id
    ORDER BY b.start_date DESC
  ");
  $stmt->execute();
  $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fetch all projects
  $projects_stmt = $pdo->query("SELECT id, project FROM projects ORDER BY project ASC");
  $projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fetch all batches (for JS filter)
  $batches_stmt = $pdo->query("SELECT id, code, project_id FROM batches ORDER BY start_date DESC");
  $batches_all = $batches_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>



<div class="container-fluid mt-4">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Batch List</h5>
    </div>

    <!-- Project & Batch Dropdown Filters -->
    <div class="p-3">
      <div class="row g-3">
        <div class="col-md-4">
          <label for="projectSelect" class="form-label">Select Project</label>
          <select id="projectSelect" class="form-select">
            <option value="">-- Select Project --</option>
            <?php foreach ($projects as $proj): ?>
              <option value="<?= $proj['id'] ?>"><?= htmlspecialchars($proj['project']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="batchSelect" class="form-label">Select Batch</label>
          <select id="batchSelect" class="form-select" disabled>
            <option value="">-- Select Batch --</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Batch Table -->
    
  </div>
</div>

<!-- JS for dynamic dropdowns -->
<script>
  const batches = <?= json_encode($batches_all) ?>;

  const projectSelect = document.getElementById('projectSelect');
  const batchSelect = document.getElementById('batchSelect');

  projectSelect.addEventListener('change', function () {
    const selectedProjectId = parseInt(this.value);
    batchSelect.innerHTML = '<option value="">-- Select Batch --</option>';

    if (!selectedProjectId) {
      batchSelect.disabled = true;
      return;
    }

    const filteredBatches = batches.filter(batch => parseInt(batch.project_id) === selectedProjectId);

    if (filteredBatches.length === 0) {
      const opt = document.createElement('option');
      opt.textContent = 'No batches available';
      opt.disabled = true;
      batchSelect.appendChild(opt);
    } else {
      filteredBatches.forEach(batch => {
        const opt = document.createElement('option');
        opt.value = batch.id;
        opt.textContent = batch.code;
        batchSelect.appendChild(opt);
      });
    }

    batchSelect.disabled = false;
  });

  batchSelect.addEventListener('change', function () {
    const batchId = this.value;
    if (batchId) {
      window.location.href = 'students-by-batch.php?batch_id=' + batchId;
    }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</main>
