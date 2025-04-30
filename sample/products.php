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

// Fetch products
$sql_products = "SELECT p.id, p.name, p.price, p.quantity, c.name AS category_name 
                 FROM products p
                 JOIN categories c ON p.category_id = c.id
                 ORDER BY p.name ASC";
$result_products = $conn->query($sql_products);
$products = $result_products ? $result_products->fetch_all(MYSQLI_ASSOC) : [];

// Fetch categories for filter
$sql_categories = "SELECT id, name FROM categories";
$result_categories = $conn->query($sql_categories);
$categories = $result_categories ? $result_categories->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Sari-Sari Store Inventory</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4a6741;
            --primary-hover: #3a5331;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: #fff;
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

        .content-wrapper {
            margin-left: 20px; /* Adjusted to bring content closer to the sidebar */
            padding: 20px;
            flex: 1;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            display: block;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .product-card {
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .low-stock {
            border-left: 4px solid #dc3545;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
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
                            <h1 class="h3 mb-0">Products</h1>
                            <a href="product_form.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Add New Product
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Filters and Search -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" placeholder="Search products...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="categoryFilter">
                                            <option value="">All Categories</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="stockFilter">
                                            <option value="">All Stock Levels</option>
                                            <option value="low">Low Stock</option>
                                            <option value="out">Out of Stock</option>
                                            <option value="available">Available</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="btn-group view-toggle" role="group">
                                            <input type="radio" class="btn-check" name="viewOptions" id="gridView" autocomplete="off" checked>
                                            <label class="btn btn-outline-primary" for="gridView"><i class="bi bi-grid-3x3-gap"></i></label>
                                            
                                            <input type="radio" class="btn-check" name="viewOptions" id="listView" autocomplete="off">
                                            <label class="btn btn-outline-primary" for="listView"><i class="bi bi-list-ul"></i></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid View -->
                <div class="row" id="productsGrid">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                            <div class="card product-card <?php echo $product['quantity'] <= 5 ? 'low-stock' : ''; ?>">
                                <span class="badge bg-<?php echo $product['quantity'] <= 5 ? 'danger' : 'success'; ?> stock-badge">
                                    <?php echo $product['quantity'] <= 5 ? 'Low Stock' : 'In Stock'; ?>
                                </span>
                                <img src="https://via.placeholder.com/300" class="card-img-top product-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">₱<?php echo number_format($product['price'], 2); ?></span>
                                        <span class="badge bg-info"><?php echo $product['quantity']; ?> units</span>
                                    </div>
                                    <div class="btn-group w-100">
                                        <a href="product_form.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <a href="transaction_form.php?product_id=<?php echo $product['id']; ?>&type=add" class="btn btn-sm btn-outline-success"><i class="bi bi-plus-circle"></i></a>
                                        <a href="transaction_form.php?product_id=<?php echo $product['id']; ?>&type=remove" class="btn btn-sm btn-outline-warning"><i class="bi bi-dash-circle"></i></a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $product['id']; ?>)"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <div class="row">
                    <div class="col-md-12">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this product? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const gridView = document.getElementById("gridView");
        const listView = document.getElementById("listView");
        const productsGrid = document.getElementById("productsGrid");
        const productsList = document.getElementById("productsList");

        // Show grid view by default
        gridView.addEventListener("change", () => {
            productsGrid.classList.remove("d-none");
            productsList.classList.add("d-none");
        });

        // Show list view when toggled
        listView.addEventListener("change", () => {
            productsGrid.classList.add("d-none");
            productsList.classList.remove("d-none");
        });
    });
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
            
            // Delete confirmation
            let productIdToDelete = null;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            
            window.confirmDelete = (productId) => {
                productIdToDelete = productId;
                deleteModal.show();
            };
            
            confirmDeleteBtn.addEventListener('click', () => {
                if (productIdToDelete) {
                    console.log(`Deleting product with ID: ${productIdToDelete}`);
                    deleteModal.hide();
                    productIdToDelete = null;
                }
            });
        });
    </script>
</body>
</html>