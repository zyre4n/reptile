<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Include necessary files
require_once 'config/database.php';
require_once 'config/app_config.php';

// Fetch product count
$sql_products = "SELECT COUNT(*) as total FROM products";
$result_products = $conn->query($sql_products);
$product_count = $result_products ? $result_products->fetch_assoc()['total'] : 0;

// Fetch category count
$sql_categories = "SELECT COUNT(*) as total FROM categories";
$result_categories = $conn->query($sql_categories);
$category_count = $result_categories ? $result_categories->fetch_assoc()['total'] : 0;

// Fetch low stock products
$sql_low_stock = "SELECT name, quantity, category_id FROM products WHERE quantity < ?";
$stmt_low_stock = $conn->prepare($sql_low_stock);
$stmt_low_stock->bind_param("i", $low_stock_threshold);
$stmt_low_stock->execute();
$result_low_stock = $stmt_low_stock->get_result();
$low_stock_products = $result_low_stock ? $result_low_stock->fetch_all(MYSQLI_ASSOC) : [];

// Fetch recent transactions
$sql_recent_transactions = "SELECT t.created_at, t.type, t.amount, u.username 
                            FROM transactions t
                            JOIN users u ON t.user_id = u.id
                            ORDER BY t.created_at DESC LIMIT 5";
$result_recent_transactions = $conn->query($sql_recent_transactions);
$recent_transactions = $result_recent_transactions ? $result_recent_transactions->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sari-Sari Store Inventory</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center page-header">
                            <h1 class="h3 mb-0">Dashboard</h1>
                            <div class="d-flex align-items-center">
                                <span class="me-2">Welcome, <?php echo htmlspecialchars($username); ?></span>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-person-circle me-1"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Store Information -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Sari-Sari Store</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Address:</strong> Bgry. Napo, Sta. Cruz, Marinduque, Philippines</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Contact:</strong> +63 912 345 6789</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Email:</strong> info@saretailstore.com</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light h-100">
                            <div class="card-body stat-card">
                                <h5 class="card-title">Total Products</h5>
                                <p class="display-4 text-primary"><?php echo $product_count; ?></p>
                                <div class="mt-3">
                                    <span class="badge bg-success">+5 this week</span>
                                </div>
                                <a href="products.php" class="btn btn-sm btn-primary mt-3">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light h-100">
                            <div class="card-body stat-card">
                                <h5 class="card-title">Categories</h5>
                                <p class="display-4 text-success"><?php echo $category_count; ?></p>
                                <div class="mt-3">
                                    <span class="badge bg-success">+2 this week</span>
                                </div>
                                <a href="categories.php" class="btn btn-sm btn-success mt-3">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light h-100">
                            <div class="card-body stat-card">
                                <h5 class="card-title">Low Stock Items</h5>
                                <p class="display-4 text-danger"><?php echo count($low_stock_products); ?></p>
                                <div class="mt-3">
                                    <span class="badge bg-success">needs attention</span>
                                </div>
                                <a href="products.php?filter=low_stock" class="btn btn-sm btn-danger mt-3">View All</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Summary -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Transactions</h5>
                                <a href="transactions.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recent_transactions)): ?>
                                                <?php foreach ($recent_transactions as $transaction): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                                                        <td><?php echo htmlspecialchars($transaction['type']); ?></td>
                                                        <td>₱<?php echo number_format($transaction['amount'], 2); ?></td>
                                                        <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No recent transactions found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Inventory Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Beverages</span>
                                        <span>85%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Canned Goods</span>
                                        <span>65%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Snacks</span>
                                        <span>45%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: 45%;" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Household</span>
                                        <span>70%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: 70%;" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Personal Care</span>
                                        <span>90%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: 90%;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <a href="product_form.php" class="btn btn-primary d-block">
                                            <i class="bi bi-plus-circle me-2"></i>Add New Product
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="category_form.php" class="btn btn-success d-block">
                                            <i class="bi bi-plus-circle me-2"></i>Add New Category
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="transaction_form.php?type=add" class="btn btn-info d-block text-white">
                                            <i class="bi bi-arrow-down-circle me-2"></i>Stock In
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="transaction_form.php?type=remove" class="btn btn-warning d-block">
                                            <i class="bi bi-arrow-up-circle me-2"></i>Stock Out
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Low Stock Alert</h5>
                                <span class="badge bg-danger"><?php echo count($low_stock_products); ?> items</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($low_stock_products)): ?>
                                                <?php foreach ($low_stock_products as $product): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                        <td><span class="badge bg-danger"><?php echo $product['quantity']; ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="2" class="text-center">No low stock items found.</td>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>