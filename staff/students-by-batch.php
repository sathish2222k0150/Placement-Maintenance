<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$batch_id = isset($_GET['batch_id']) ? (int) $_GET['batch_id'] : 0;
if ($batch_id <= 0)
  die("Invalid batch ID.");

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $delete_id = (int) $_POST['delete_id'];
  $delete_stmt = $pdo->prepare("DELETE FROM students WHERE id = ? AND user_id = ?");
  $delete_stmt->execute([$delete_id, $user_id]);
  header("Location: students_by_batch.php?batch_id=$batch_id");
  exit;
}

// Search + Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE batch_id = :batch_id AND user_id = :user_id";
$params = [':batch_id' => $batch_id, ':user_id' => $user_id];

if ($search !== '') {
  $where .= " AND (name LIKE :search OR reg_no LIKE :search OR contact LIKE :search)";
  $params[':search'] = '%' . $search . '%';
}

// Total count (unchanged)
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM students $where");
$count_stmt->execute($params);
$total_students = $count_stmt->fetchColumn();
$total_pages = ceil($total_students / $limit);

// ✅ Safe SQL with limit/offset inline
$sql = "SELECT * FROM students $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Students in Batch</title>
  <link rel="stylesheet" href="../css/adminlte.css" />
  <link rel="preload" href="../css/adminlte.css" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
    onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
    crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />
  <link rel="stylesheet" href="../css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
    integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .table-responsive {
      max-height: 600px;
      overflow-y: auto;
    }

    .btn-sm {
      padding: 2px 6px;
      font-size: 0.8rem;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="container-fluid mt-4">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Students in Batch ID: <?= htmlspecialchars($batch_id) ?></h5>
          <form method="get" class="d-flex">
            <input type="hidden" name="batch_id" value="<?= $batch_id ?>">
          </form>
        </div>

        <div class="card-body table-responsive">
          <?php if (!empty($students)): ?>
            <table class="table table-bordered table-striped table-hover text-nowrap">
              <thead class="table-primary sticky-top">
                <tr>
                  <th>S.No</th>
                  <th>Name</th>
                  <th>Reg. No</th>
                  <th>Aadhar</th>
                  <th>Gender</th>
                  <th>DOB</th>
                  <th>Contact</th>
                  <th>District</th>
                  <th>Village</th>
                  <th>Qualification</th>
                  <th>Course</th>
                  <th>Batch End</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($students as $i => $student): ?>
                  <tr>
                    <td><?= ($offset + $i + 1) ?></td>
                    <td><?= htmlspecialchars($student['name']) ?></td>
                    <td><?= htmlspecialchars($student['reg_no']) ?></td>
                    <td><?= htmlspecialchars($student['aadhar']) ?></td>
                    <td><?= htmlspecialchars($student['gender']) ?></td>
                    <td><?= date('d M Y', strtotime($student['dob'])) ?></td>
                    <td><?= htmlspecialchars($student['contact']) ?></td>
                    <td><?= htmlspecialchars($student['district']) ?></td>
                    <td><?= htmlspecialchars($student['village']) ?></td>
                    <td><?= htmlspecialchars($student['qualification']) ?></td>
                    <td><?= htmlspecialchars($student['course_name']) ?></td>
                    <td><?= date('d M Y', strtotime($student['batch_end'])) ?></td>
                    <td>
                      <a href="edit-student.php?edit=<?= $student['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#confirmDeleteModal" data-student-id="<?= $student['id'] ?>">
                        Delete
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <!-- Pagination -->
            <nav class="my-4">
              <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                  <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a class="page-link"
                      href="?batch_id=<?= $batch_id ?>&search=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
                  </li>
                <?php endfor; ?>
              </ul>
            </nav>



          <?php else: ?>
            <div class="alert alert-info">No students found in this batch for your account.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this student?
            <input type="hidden" name="delete_id" id="deleteStudentId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Yes, Delete</button>
          </div>
        </form>
      </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/adminlte.js"></script>
    <script>
      const confirmModal = document.getElementById('confirmDeleteModal');
      confirmModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const studentId = button.getAttribute('data-student-id');
        confirmModal.querySelector('#deleteStudentId').value = studentId;
      });
    </script>
</body>

</html>