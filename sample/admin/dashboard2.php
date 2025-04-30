<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin/login.php");
    exit();
}

// Initialize variables
$total_products = 0;
$total_categories = 0;
$low_stock_items = [];
$recent_transactions = [];

// Fetch total products
$sql_products = "SELECT COUNT(*) as count FROM products";
$result_products = $conn->query($sql_products);
if ($result_products && $result_products->num_rows > 0) {
    $total_products = $result_products->fetch_assoc()['count'];
}

// Fetch total categories
$sql_categories = "SELECT COUNT(*) as count FROM categories";
$result_categories = $conn->query($sql_categories);
if ($result_categories && $result_categories->num_rows > 0) {
    $total_categories = $result_categories->fetch_assoc()['count'];
}

// Fetch low stock items
$low_stock_threshold = 5; // Define the threshold for low stock
$sql_low_stock = "SELECT p.name, c.name as category, p.quantity 
                  FROM products p
                  JOIN categories c ON p.category_id = c.id
                  WHERE p.quantity < ?";
$stmt_low_stock = $conn->prepare($sql_low_stock);
$stmt_low_stock->bind_param("i", $low_stock_threshold);
$stmt_low_stock->execute();
$result_low_stock = $stmt_low_stock->get_result();
if ($result_low_stock && $result_low_stock->num_rows > 0) {
    $low_stock_items = $result_low_stock->fetch_all(MYSQLI_ASSOC);
}

// Fetch recent transactions
$sql_transactions = "SELECT t.*, p.name as product_name, u.username 
                     FROM transactions t
                     JOIN products p ON t.product_id = p.id
                     JOIN users u ON t.user_id = u.id
                     ORDER BY t.date DESC
                     LIMIT 5";
$result_transactions = $conn->query($sql_transactions);
if ($result_transactions && $result_transactions->num_rows > 0) {
    $recent_transactions = $result_transactions->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style2.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar-container">
            <nav class="sidebar">
                <div class="sidebar-header">
                    <h2>Sari-Sari Store</h2>
                    <p>Inventory Management</p>
                </div>
                <div class="sidebar-menu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="dashboard2.php" class="nav-link active">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_products.php" class="nav-link">
                                <i class="bi bi-box-seam me-2"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_categories.php" class="nav-link">
                                <i class="bi bi-tags me-2"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="transactions.php" class="nav-link">
                                <i class="bi bi-arrow-left-right me-2"></i> Transactions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_users.php" class="nav-link">
                                <i class="bi bi-people me-2"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../auth/logout.php" class="nav-link">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h1 class="h3">Dashboard</h1>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Products</h5>
                                <p class="display-4 text-primary"><?php echo $total_products; ?></p>
                                <a href="manage_products.php" class="btn btn-primary btn-sm">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Categories</h5>
                                <p class="display-4 text-success"><?php echo $total_categories; ?></p>
                                <a href="manage_categories.php" class="btn btn-success btn-sm">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Low Stock Items</h5>
                                <p class="display-4 text-danger"><?php echo count($low_stock_items); ?></p>
                                <a href="manage_products.php?filter=low_stock" class="btn btn-danger btn-sm">View All</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Transactions</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recent_transactions)): ?>
                                            <?php foreach ($recent_transactions as $transaction): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($transaction['quantity']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $transaction['quantity'] > 0 ? 'success' : 'warning'; ?>">
                                                            <?php echo $transaction['quantity'] > 0 ? 'Stock In' : 'Stock Out'; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($transaction['date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No recent transactions</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Items -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Low Stock Items</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($low_stock_items)): ?>
                                            <?php foreach ($low_stock_items as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                                    <td class="text-danger"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center">No low stock items</td>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>