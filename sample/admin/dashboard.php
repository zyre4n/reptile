<?php
session_start();
include '../config/database.php';
include '../config/app_config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../admin-login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get dashboard statistics
$sql_products = "SELECT COUNT(*) as count FROM products";
$result_products = $conn->query($sql_products);
$product_count = $result_products->fetch_assoc()['count'];

$sql_categories = "SELECT COUNT(*) as count FROM categories";
$result_categories = $conn->query($sql_categories);
$category_count = $result_categories->fetch_assoc()['count'];

$sql_users = "SELECT COUNT(*) as count FROM users";
$result_users = $conn->query($sql_users);
$user_count = $result_users->fetch_assoc()['count'];

$sql_transactions = "SELECT COUNT(*) as count FROM inventory_transactions";
$result_transactions = $conn->query($sql_transactions);
$transaction_count = $result_transactions->fetch_assoc()['count'];

// Get recent activities
$sql_activities = "SELECT al.*, u.username 
                  FROM audit_log al
                  LEFT JOIN users u ON al.user_id = u.id
                  ORDER BY al.created_at DESC
                  LIMIT 10";
$result_activities = $conn->query($sql_activities);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo get_config('store_name', 'Sari-Sari Store'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --admin-primary: #dc3545;
            --admin-hover: #bb2d3b;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px 15px;
            background-color: var(--admin-primary);
            text-align: center;
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            transition: all 0.3s;
        }
        
        .sidebar-menu .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu .nav-link.active {
            color: #fff;
            background-color: var(--admin-primary);
        }
        
        .sidebar-menu .nav-link i {
            margin-right: 10px;
        }
        
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--admin-primary);
        }
        
        .stat-card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            color: var(--admin-primary);
        }
        
        .stat-card .count {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-card .title {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .activity-card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .activity-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: bold;
        }
        
        .activity-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-item .time {
            color: #6c757d;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .content {
                margin-left: 0;
            }
            
            .content.active {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
            <p class="mb-0"><?php echo get_config('store_name', 'Sari-Sari Store'); ?></p>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link">
                        <i class="bi bi-box-seam"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="categories.php" class="nav-link">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">
                        <i class="bi bi-people"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="transactions.php" class="nav-link">
                        <i class="bi bi-arrow-left-right"></i> Transactions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link">
                        <i class="bi bi-bar-chart"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../dashboard.php" class="nav-link">
                        <i class="bi bi-shop"></i> Store Front
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Content -->
    <div class="content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light mb-4">
            <div class="container-fluid">
                <button class="btn btn-outline-secondary me-2" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <span class="navbar-brand">Admin Dashboard</span>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i> <?php echo $username; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Dashboard Content -->
        <div class="container-fluid">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="count"><?php echo $product_count; ?></div>
                        <div class="title">Products</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="icon">
                            <i class="bi bi-tags"></i>
                        </div>
                        <div class="count"><?php echo $category_count; ?></div>
                        <div class="title">Categories</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="count"><?php echo $user_count; ?></div>
                        <div class="title">Users</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="icon">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <div class="count"><?php echo $transaction_count; ?></div>
                        <div class="title">Transactions</div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="row">
                <div class="col-md-12">
                    <div class="activity-card">
                        <div class="card-header">
                            <i class="bi bi-activity me-2"></i> Recent Activities
                        </div>
                        <div class="card-body p-0">
                            <?php if ($result_activities && $result_activities->num_rows > 0): ?>
                                <?php while ($activity = $result_activities->fetch_assoc()): ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo $activity['username'] ?? 'System'; ?></strong> 
                                                <?php echo $activity['action']; ?> 
                                                <?php echo $activity['entity_type']; ?>
                                                <?php if ($activity['entity_id']): ?>
                                                    #<?php echo $activity['entity_id']; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="time">
                                                <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                            </div>
                                        </div>
                                        <?php if ($activity['details']): ?>
                                            <div class="text-muted small"><?php echo $activity['details']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="activity-item">
                                    <div class="text-center py-3">No recent activities found.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                content.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
