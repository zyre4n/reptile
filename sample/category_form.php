<?php
session_start();
include_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$category_id = '';
$name = '';
$description = '';
$status = 'Active';

// Check if editing an existing category
if (isset($_GET['id'])) {
    $category_id = $conn->real_escape_string($_GET['id']);
    $sql = "SELECT * FROM categories WHERE id = '$category_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
        $name = $category['name'];
        $description = $category['description'];
        $status = $category['status'];
    } else {
        header("Location: categories.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $status = $conn->real_escape_string($_POST['status']);

    if (!empty($name)) {
        if (!empty($category_id)) {
            // Update existing category
            $sql = "UPDATE categories SET name = '$name', description = '$description', status = '$status' WHERE id = '$category_id'";
            if ($conn->query($sql)) {
                header("Location: categories.php");
                exit();
            } else {
                $error_message = "Error updating category: " . $conn->error;
            }
        } else {
            // Insert new category
            $sql = "INSERT INTO categories (name, description, status) VALUES ('$name', '$description', '$status')";
            if ($conn->query($sql)) {
                header("Location: categories.php");
                exit();
            } else {
                $error_message = "Error adding category: " . $conn->error;
            }
        }
    } else {
        $error_message = "Category name is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($category_id) ? 'Edit Category' : 'Add Category'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/category_form.css">
</head>
</head>
<body>
    <div class="container">
        <h1><?php echo isset($category_id) ? 'Edit Category' : 'Add Category'; ?></h1>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="Active" <?php echo $status == 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $status == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo isset($category_id) ? 'Update Category' : 'Add Category'; ?></button>
            <a href="categories.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>