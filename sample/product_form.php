<?php
session_start();
include_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$product_id = '';
$name = '';
$description = '';
$price = '';
$quantity = '';
$category_id = '';
$is_edit = false;

// Check if editing an existing product
if (isset($_GET['id'])) {
    $product_id = $conn->real_escape_string($_GET['id']);
    $sql = "SELECT * FROM products WHERE id = '$product_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $name = $product['name'];
        $description = $product['description'];
        $price = $product['price'];
        $quantity = $product['quantity'];
        $category_id = $product['category_id'];
        $is_edit = true;
    } else {
        header("Location: products.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $price = $conn->real_escape_string(trim($_POST['price']));
    $quantity = $conn->real_escape_string(trim($_POST['quantity']));
    $category_id = $conn->real_escape_string($_POST['category_id']);

    if (!empty($name) && !empty($price) && !empty($quantity) && !empty($category_id)) {
        if ($is_edit) {
            // Update existing product
            $sql = "UPDATE products SET 
                        name = '$name', 
                        description = '$description', 
                        price = '$price', 
                        quantity = '$quantity', 
                        category_id = '$category_id' 
                    WHERE id = '$product_id'";
            if ($conn->query($sql)) {
                header("Location: products.php");
                exit();
            } else {
                $error_message = "Error updating product: " . $conn->error;
            }
        } else {
            // Insert new product
            $sql = "INSERT INTO products (name, description, price, quantity, category_id) 
                    VALUES ('$name', '$description', '$price', '$quantity', '$category_id')";
            if ($conn->query($sql)) {
                header("Location: products.php");
                exit();
            } else {
                $error_message = "Error adding product: " . $conn->error;
            }
        }
    } else {
        $error_message = "All fields are required.";
    }
}

// Fetch categories for the dropdown
$sql_categories = "SELECT id, name FROM categories WHERE status = 'Active' ORDER BY name";
$result_categories = $conn->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Product' : 'Add Product'; ?></title>
    <link rel="stylesheet" href="assets/css/product_form.css">
</head>
<body>
    <div class="container">
        <h1><?php echo $is_edit ? 'Edit Product' : 'Add Product'; ?></h1>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($price); ?>" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>" required>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $category_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="productImage" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="productImage" name="productImage" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Update Product' : 'Add Product'; ?></button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>