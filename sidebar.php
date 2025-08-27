<?php
require 'config.php'; // include DB connection
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

?>

<aside class="app-sidebar bg-body-secondary shadow w-280" data-bs-theme="dark">
  <div class="sidebar-brand">
  <a href="./index.html" class="brand-link d-flex align-items-center gap-2 p-2">
    <img src="./assets/logo-sharadha.jpg" 
         alt="Sharadha Logo" 
         class="brand-image opacity-75 shadow rounded-3" 
         style="height: 80px; width: auto;" />
    <span class="brand-text fw-semibold" style="font-size: 18px;">Sharadha Skill Academy</span>
  </a>
</div>


  <div class="sidebar-wrapper">
    <nav class="mt-3">
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation"
        data-accordion="false" id="navigation">
        <li class="nav-item">
          <a href="./dashboard.php" class="nav-link">
            <i class="nav-icon bi bi-speedometer2"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="create-project.php" class="nav-link">
            <i class="nav-icon bi bi-people-fill"></i>
            <p>Create Project</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="create-batch.php" class="nav-link">
            <i class="nav-icon bi bi-people-fill"></i>
            <p>Create Batch</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="details.php" class="nav-link">
            <i class="nav-icon bi bi-person-lines-fill"></i>
            <p>Students Details</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="upload-students.php" class="nav-link">
            <i class="nav-icon bi bi-person-lines-fill"></i>
            <p>Upload Students Details</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="filters.php" class="nav-link">
            <i class="nav-icon bi bi-person-lines-fill"></i>
            <p>Students Filters</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="user-register.php" class="nav-link">
            <i class="nav-icon bi bi-person-plus-fill"></i>
            <p>Staff Registration</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="user-details.php" class="nav-link">
            <i class="nav-icon bi bi-person-lines-fill"></i>
            <p>Staff - Details</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="logout.php" class="nav-link">
            <i class="nav-icon bi bi-box-arrow-right"></i>
            <p>Logout</p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>