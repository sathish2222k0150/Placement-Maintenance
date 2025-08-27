<?php
require '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>
<aside class="app-sidebar bg-body-secondary shadow w-280" data-bs-theme="dark">
  <div class="sidebar-brand">
    <a href="" class="brand-link d-flex align-items-center p-8 ps-3 gap-2">
      <img src="../assets/logo-sharadha.jpg" alt="Logo" class="brand-image opacity-75 shadow rounded-3"
        style="height: 40px;" />
      <span class="brand-text fw-semibold text-white fs-6">Sharadha Skill Academy</span>
    </a>
  </div>

  <div class="sidebar-wrapper">
    <nav class="mt-3">
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation"
        data-accordion="false" id="navigation">

        <li class="nav-item">
          <a href="staff-dashboard.php" class="nav-link">
            <i class="nav-icon bi bi-speedometer2"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="details.php" class="nav-link">
            <i class="nav-icon bi bi-list-ul"></i>
            <p>All Details</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="student-details.php" class="nav-link">
            <i class="nav-icon bi bi-person-vcard-fill"></i>
            <p>Students Personal <br> Details</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="add-student.php" class="nav-link">
            <i class="nav-icon bi bi-person-plus-fill"></i>
            <p>Add Student</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="placement.php" class="nav-link">
            <i class="nav-icon bi bi-briefcase-fill"></i>
            <p>Add Placement Report</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="placement-details.php" class="nav-link">
            <i class="nav-icon bi bi-clipboard-data-fill"></i>
            <p>Placement Details</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="all-details.php" class="nav-link">
            <i class="nav-icon bi bi-archive-fill"></i>
            <p>All Details Placement</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../logout.php" class="nav-link">
            <i class="nav-icon bi bi-box-arrow-right"></i>
            <p>Logout</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>
