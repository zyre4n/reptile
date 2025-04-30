<?php
require_once __DIR__ . '/../config/app_config.php'; // Include app_config.php

/**
 * Helper function to get the current page name
 * 
 * @return string Current page name
 */
function getCurrentPage() {
    return basename($_SERVER['PHP_SELF']);
}
?>

<div class="sidebar-container bg-light" style="width: 250px; height: 100vh;">
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header text-center py-3">
            <h2><?php echo htmlspecialchars($app_name); ?></h2>
            <p>Inventory Management</p>
        </div>
        <hr>
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo (getCurrentPage() == 'dashboard.php') ? 'active text-primary' : 'link-dark'; ?>">
                        <i class="bi bi-speedometer2 me-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link <?php echo (getCurrentPage() == 'products.php') ? 'active text-primary' : 'link-dark'; ?>">
                        <i class="bi bi-box-seam me-2"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="categories.php" class="nav-link <?php echo (getCurrentPage() == 'categories.php') ? 'active text-primary' : 'link-dark'; ?>">
                        <i class="bi bi-tags me-2"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?php echo (getCurrentPage() == 'users.php') ? 'active text-primary' : 'link-dark'; ?>">
                        <i class="bi bi-people me-2"></i>
                        <span>Users</span>
                    </a>
                </li>
            </ul>
        </div>
        <hr>
        <div class="sidebar-footer text-center">
            <p>Logged in as: <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></p>
            <a href="auth/logout.php" class="btn btn-danger btn-sm mt-2">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </nav>
</div>