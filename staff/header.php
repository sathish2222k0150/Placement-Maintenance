<?php
require __DIR__ . '/../config.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}
?>
<script src="https://cdn.tailwindcss.com"></script>

<nav class="app-header navbar navbar-expand bg-body">
  <div class="container-fluid">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
          <i class="bi bi-list"></i>
        </a>
      </li>
      <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Home</a></li>
      <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>
    </ul>

    <ul class="navbar-nav ms-auto">
      <li class="nav-item">
        <a class="nav-link" href="#" data-lte-toggle="fullscreen">
          <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
          <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
        </a>
      </li>

      <!-- User Menu -->
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
          <img src="../assets/user2-160x160.jpg" class="user-image rounded-circle shadow" alt="User Image" style="height: 32px; width: 32px;" />
          <span class="d-none d-md-inline fw-semibold"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Logged-in User'); ?></span>
        </a>

        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end text-center">
          <li class="user-header text-bg-primary d-flex flex-column align-items-center">
            <img src="../assets/user2-160x160.jpg" class="rounded-circle shadow mb-2" alt="User Image" style="height: 80px; width: 80px;" />
            <p class="mb-0 fw-bold">
              <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Logged-in User'); ?>
            </p>
          </li>
          <li class="user-footer d-flex justify-content-center gap-3 p-2">
            <a href="#" class="btn btn-default btn-flat">Profile</a>
            <a href="../logout.php" class="btn btn-default btn-flat">Sign out</a>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</nav>
