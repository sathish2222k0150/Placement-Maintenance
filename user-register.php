<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role     = $_POST['role'];
    $course   = ($role === 'staff') ? trim($_POST['course']) : null;

    if ($name && $email && $password && in_array($role, ['admin', 'staff'])) {
        if ($role === 'staff' && empty($course)) {
            $_SESSION['error'] = "Course is required for staff.";
            header("Location: user-register.php");
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, course) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $role, $course]);
            $_SESSION['success'] = "User registered successfully.";
            header("Location: user-register.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['error'] = "Email already exists.";
            } else {
                $_SESSION['error'] = "Registration failed: " . $e->getMessage();
            }
            header("Location: user-register.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "All fields are required and role must be valid.";
        header("Location: user-register.php");
        exit;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Sharadha Skill Academy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="./css/adminlte.css" />
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">
<?php include 'sidebar.php' ?>
<?php include 'header.php' ?>

<div class="fixed-content-wrapper d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <div class="register-box w-100" style="max-width: 400px;">
    <div class="register-logo text-center mb-3">
      <a href="#" class="h3 text-decoration-none">Sharadha Skill Academy</a>
    </div>
    <div class="card shadow">
      <div class="card-body register-card-body">
        <p class="register-box-msg">Register a New User</p>

        <?php if ($success): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="user-register.php" method="post">
          <div class="input-group mb-3">
            <input type="text" name="full_name" class="form-control" placeholder="Full Name" required />
            <div class="input-group-text"><span class="bi bi-person"></span></div>
          </div>
          <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required />
            <div class="input-group-text"><span class="bi bi-envelope"></span></div>
          </div>
          <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required />
            <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
          </div>
          <div class="mb-3">
            <select name="role" class="form-select" required onchange="toggleCourseField(this.value)">
              <option value="" disabled selected>Select Role</option>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
            </select>
          </div>
          <div class="input-group mb-3" id="course-group" style="display: none;">
            <input type="text" name="course" class="form-control" placeholder="Course (for staff only)" />
            <div class="input-group-text"><span class="bi bi-journal-bookmark-fill"></span></div>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Register</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php' ?>

<script>
function toggleCourseField(role) {
  document.getElementById('course-group').style.display = (role === 'staff') ? 'flex' : 'none';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
<script src="./js/adminlte.js"></script>
</body>
</html>
