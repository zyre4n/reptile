<div class="sidebar-container">
    <nav class="sidebar admin-sidebar" id="sidebar">
        <div class="sidebar-header admin-header">
            <h2><?php echo get_config('store_name', 'Sari-Sari Store'); ?></h2>
            <p>Admin Control Panel</p>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2 me-2"></i>
                        <span>Admin Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' || basename($_SERVER['PHP_SELF']) == 'user_form.php' ? 'active' : ''; ?>">
                        <i class="bi bi-people me-2"></i>
                        <span>User Management</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'product_form.php' ? 'active' : ''; ?>">
                        <i class="bi bi-box-seam me-2"></i>
                        <span>Products</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' || basename($_SERVER['PHP_SELF']) == 'category_form.php' ? 'active' : ''; ?>">
                        <i class="bi bi-tags me-2"></i>
                        <span>Categories</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="transactions.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
                        <i class="bi bi-arrow-left-right me-2"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                        <i class="bi bi-bar-chart me-2"></i>
                        <span>Reports</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                        <i class="bi bi-gear me-2"></i>
                        <span>System Settings</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../dashboard.php" class="nav-link">
                        <i class="bi bi-arrow-left-circle me-2"></i>
                        <span>Go to Store</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <p>Logged in as: <?php echo $_SESSION['username']; ?></p>
                <p>Role: <?php echo $_SESSION['role']; ?></p>
            </div>
            <a href="logout.php" class="btn btn-danger btn-sm mt-2">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </nav>
    
    <button class="btn btn-danger sidebar-toggler" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
</div>
