<?php
require 'config.php';
session_start();

// Redirect if no ID provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: user-details.php');
    exit;
}

$user_id = intval($_GET['id']);
$message = '';

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$edit_user = $stmt->fetch(PDO::FETCH_ASSOC); // renamed variable

if (!$edit_user) {
    echo "User not found.";
    exit;
}

// Handle Delete
if (isset($_POST['delete'])) {
    $del_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $del_stmt->execute([$user_id]);
    header("Location: user-details.php?msg=User deleted successfully");
    exit;
}

// Handle Update
if (isset($_POST['update'])) {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $role      = trim($_POST['role']);
    $course    = trim($_POST['course']);

    if (empty($full_name) || empty($email) || empty($role)) {
        $message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Check for duplicate email (excluding current user)
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->execute([$email, $user_id]);
        if ($check_stmt->rowCount() > 0) {
            $message = "Email already exists for another user.";
        } else {
            $update_stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, course = ? WHERE id = ?");
            $update_stmt->execute([$full_name, $email, $role, $course, $user_id]);
            header("Location: user-details.php?msg=User updated successfully");
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Edit User - Sharadha Skill Academy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />

  <!-- CSS -->
  <link rel="preload" href="./css/adminlte.css" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="./css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" crossorigin="anonymous" />
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">

  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <!-- Main content -->
  <main class="app-main">
    <div class="container-fluid px-4 py-4">
      <h3>Edit User</h3>

      <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="post" class="mt-3">
        <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($edit_user['full_name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role *</label>
            <select name="role" class="form-select" required>
                <option value="admin" <?= ($edit_user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="staff" <?= ($edit_user['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Course</label>
            <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($edit_user['course'] ?? '') ?>">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" name="update" class="btn btn-primary">Update</button>
            <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
            <a href="view-users.php" class="btn btn-secondary">Back</a>
        </div>
      </form>
    </div>
  </main>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="./js/adminlte.js"></script>
</body>
</html>
