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

// Fetch transaction data
$sql_transactions = "SELECT t.id, t.created_at, t.type, t.quantity, t.notes, p.name AS product_name, u.username 
                     FROM transactions t
                     JOIN products p ON t.product_id = p.id
                     JOIN users u ON t.user_id = u.id
                     ORDER BY t.created_at DESC";
$result_transactions = $conn->query($sql_transactions);
$transactions = $result_transactions ? $result_transactions->fetch_all(MYSQLI_ASSOC) : [];

// Fetch transaction summary
$sql_summary = "SELECT 
                    (SELECT COUNT(*) FROM transactions) AS total_transactions,
                    (SELECT COUNT(*) FROM transactions WHERE type = 'stock_in') AS stock_in,
                    (SELECT COUNT(*) FROM transactions WHERE type = 'stock_out') AS stock_out,
                    (SELECT COUNT(*) FROM transactions WHERE type LIKE 'adjustment%') AS adjustments";
$result_summary = $conn->query($sql_summary);
$summary = $result_summary ? $result_summary->fetch_assoc() : [
    'total_transactions' => 0,
    'stock_in' => 0,
    'stock_out' => 0,
    'adjustments' => 0,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Sari-Sari Store Inventory</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4a6741;
            --primary-hover: #3a5331;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            overflow-x: hidden;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .content-wrapper {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
            transition: margin-left 0.3s;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: transform 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #fff;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        
        .stat-card .display-4 {
            font-weight: 600;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(74, 103, 65, 0.05);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .content-wrapper {
                margin-left: 0;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .content-wrapper.sidebar-open {
                margin-left: 250px;
            }
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
    </style>
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
                            <h1 class="h3 mb-0">Inventory Transactions</h1>
                            <div>
                                <a href="transaction_form.php?type=add" class="btn btn-success me-2">
                                    <i class="bi bi-plus-circle me-1"></i> Stock In
                                </a>
                                <a href="transaction_form.php?type=remove" class="btn btn-warning">
                                    <i class="bi bi-dash-circle me-1"></i> Stock Out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Transactions Summary -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Transactions</h6>
                                <p class="display-5 text-primary"><?php echo $summary['total_transactions']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Stock In</h6>
                                <p class="display-5 text-success"><?php echo $summary['stock_in']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Stock Out</h6>
                                <p class="display-5 text-warning"><?php echo $summary['stock_out']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Adjustments</h6>
                                <p class="display-5 text-info"><?php echo $summary['adjustments']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Transactions Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Transaction History</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Date & Time</th>
                                                <th>Product</th>
                                                <th>Quantity Change</th>
                                                <th>Type</th>
                                                <th>User</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($transactions)): ?>
                                                <?php foreach ($transactions as $transaction): ?>
                                                    <tr>
                                                        <td><?php echo $transaction['id']; ?></td>
                                                        <td><?php echo $transaction['created_at']; ?></td>
                                                        <td><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                                                        <td class="<?php echo $transaction['quantity'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                            <?php echo $transaction['quantity'] > 0 ? '+' : ''; ?><?php echo $transaction['quantity']; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $transaction['type'] === 'stock_in' ? 'success' : ($transaction['type'] === 'stock_out' ? 'warning' : 'info'); ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $transaction['type'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                                        <td><?php echo htmlspecialchars($transaction['notes']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No transactions found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Export Options -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Export Transactions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p>Export the current filtered transactions to a file format of your choice.</p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="btn-group">
                                            <button class="btn btn-outline-primary">
                                                <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                            </button>
                                            <button class="btn btn-outline-primary">
                                                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                            </button>
                                            <button class="btn btn-outline-primary">
                                                <i class="bi bi-file-earmark-text me-1"></i> CSV
                                            </button>
                                        </div>
                                    </div>
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