<?php
// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Set page title
$page_title = "Admin Dashboard";

// Get system statistics
$sql_users = "SELECT COUNT(*) as total FROM users";
$result_users = $conn->query($sql_users);
$user_count = $result_users->fetch_assoc()['total'];

$sql_products = "SELECT COUNT(*) as total FROM products";
$result_products = $conn->query($sql_products);
$product_count = $result_products->fetch_assoc()['total'];

$sql_categories = "SELECT COUNT(*) as total FROM categories";
$result_categories = $conn->query($sql_categories);
$category_count = $result_categories->fetch_assoc()['total'];

// Get low stock threshold from configuration
$low_stock_threshold = get_low_stock_threshold();

// Get low stock products
$sql_low_stock = "SELECT COUNT(*) as total FROM products WHERE quantity < $low_stock_threshold";
$result_low_stock = $conn->query($sql_low_stock);
$low_stock_count = $result_low_stock->fetch_assoc()['total'];

// Get recent login activities
$sql_logins = "SELECT la.username, la.ip_address, la.attempt_time, la.success, u.role 
              FROM login_attempts la
              LEFT JOIN users u ON la.username = u.username
              ORDER BY la.attempt_time DESC LIMIT 10";
$result_logins = $conn->query($sql_logins);

// Get recent system activities from audit log
$sql_audit = "SELECT al.*, u.username 
             FROM audit_log al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC LIMIT 10";
$result_audit = $conn->query($sql_audit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo get_config('store_name', 'Sari-Sari Store'); ?> Inventory</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-header {
            background-color: #dc3545;
            color: white;
        }
        
        .admin-sidebar .nav-link.active {
            background-color: rgba(220, 53, 69, 0.2);
            border-left: 4px solid #dc3545;
        }
        
        .admin-sidebar .nav-link:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        .stat-card {
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Admin Sidebar -->
        <div class="sidebar-container">
            <nav class="sidebar admin-sidebar" id="sidebar">
                <div class="sidebar-header admin-header">
                    <h2><?php echo get_config('store_name', 'Sari-Sari Store'); ?></h2>
                    <p>Admin Control Panel</p>
                </div>
                
                <div class="sidebar-menu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">
                                <i class="bi bi-speedometer2 me-2"></i>
                                <span>Admin Dashboard</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="users.php" class="nav-link">
                                <i class="bi bi-people me-2"></i>
                                <span>User Management</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="products.php" class="nav-link">
                                <i class="bi bi-box-seam me-2"></i>
                                <span>Products</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="categories.php" class="nav-link">
                                <i class="bi bi-tags me-2"></i>
                                <span>Categories</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="transactions.php" class="nav-link">
                                <i class="bi bi-arrow-left-right me-2"></i>
                                <span>Transactions</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="reports.php" class="nav-link">
                                <i class="bi bi-bar-chart me-2"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="settings.php" class="nav-link">
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
                        <p>Logged in as: <?php echo $username; ?></p>
                        <p>Role: <?php echo $role; ?></p>
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
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center page-header">
                            <h1 class="h3 mb-0">Admin Dashboard</h1>
                            <div class="d-flex align-items-center">
                                <span class="me-2">Welcome, <?php echo $username; ?> (<?php echo $role; ?>)</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white stat-card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Users</h5>
                                <p class="display-4"><?php echo $user_count; ?></p>
                                <a href="users.php" class="btn btn-sm btn-light">Manage Users</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-success text-white stat-card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Products</h5>
                                <p class="display-4"><?php echo $product_count; ?></p>
                                <a href="products.php" class="btn btn-sm btn-light">Manage Products</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-info text-white stat-card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Categories</h5>
                                <p class="display-4"><?php echo $category_count; ?></p>
                                <a href="categories.php" class="btn btn-sm btn-light">Manage Categories</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-danger text-white stat-card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Low Stock Items</h5>
                                <p class="display-4"><?php echo $low_stock_count; ?></p>
                                <a href="products.php?filter=low_stock" class="btn btn-sm btn-light">View Items</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <a href="user_form.php" class="btn btn-primary d-block">
                                            <i class="bi bi-person-plus me-2"></i>Add New User
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="product_form.php" class="btn btn-success d-block">
                                            <i class="bi bi-plus-circle me-2"></i>Add New Product
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="category_form.php" class="btn btn-info text-white d-block">
                                            <i class="bi bi-folder-plus me-2"></i>Add New Category
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="reports.php" class="btn btn-warning d-block">
                                            <i class="bi bi-file-earmark-text me-2"></i>Generate Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Login Activities</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>IP Address</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result_logins->num_rows > 0): ?>
                                                <?php while($row = $result_logins->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $row['username']; ?></td>
                                                        <td><?php echo $row['role'] ?? 'N/A'; ?></td>
                                                        <td>
                                                            <?php if ($row['success']): ?>
                                                                <span class="badge bg-success">Success</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Failed</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo $row['ip_address']; ?></td>
                                                        <td><?php echo date('M d, H:i', strtotime($row['attempt_time'])); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No login activities found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">System Activities</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Action</th>
                                                <th>Entity</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result_audit && $result_audit->num_rows > 0): ?>
                                                <?php while($row = $result_audit->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $row['username'] ?? 'System'; ?></td>
                                                        <td><?php echo ucfirst(str_replace('_', ' ', $row['action'])); ?></td>
                                                        <td><?php echo ucfirst($row['entity_type']); ?></td>
                                                        <td><?php echo date('M d, H:i', strtotime($row['created_at'])); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No system activities found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById("sidebarToggle");
            const sidebar = document.getElementById("sidebar");
            const contentWrapper = document.querySelector(".content-wrapper");
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener("click", () => {
                    sidebar.classList.toggle("show");
                    contentWrapper.classList.toggle("sidebar-open");
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener("click", (event) => {
                const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
                const isClickOnToggler = sidebarToggle && sidebarToggle.contains(event.target);
                
                if (
                    window.innerWidth <= 768 &&
                    !isClickInsideSidebar &&
                    !isClickOnToggler &&
                    sidebar &&
                    sidebar.classList.contains("show")
                ) {
                    sidebar.classList.remove("show");
                    contentWrapper.classList.remove("sidebar-open");
                }
            });
        });
    </script>
</body>
</html>
