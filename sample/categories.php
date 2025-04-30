<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';
include_once 'config/database.php';

// Fetch categories from the database
$sql = "SELECT c.id, c.name, c.description, c.status, 
               (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count 
        FROM categories c ORDER BY c.name";
$result = $conn->query($sql);
$categories = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- Main Content -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center page-header">
                    <h1 class="h3 mb-0">Categories</h1>
                    <a href="category_form.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Category
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Categories Card -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Category List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $index => $category): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $category['product_count']; ?></span></td>
                                        <td>
                                            <?php if ($category['status'] == 'Active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="category_form.php?id=<?php echo $category['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?');">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No categories found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>