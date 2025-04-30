<?php
session_start();
include 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Check if product_id and type are provided
if (!isset($_GET['product_id']) || !isset($_GET['type'])) {
    header("Location: products.php");
    exit();
}

$product_id = $conn->real_escape_string($_GET['product_id']);
$type = $conn->real_escape_string($_GET['type']);

// Validate transaction type
if ($type != 'add' && $type != 'remove') {
    header("Location: products.php");
    exit();
}

// Get product information
$sql = "SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = '$product_id'";
$result = $conn->query($sql);

if ($result->num_rows != 1) {
    header("Location: products.php");
    exit();
}

$product = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantity = $conn->real_escape_string($_POST['quantity']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    // Validate quantity
    if (!is_numeric($quantity) || $quantity <= 0) {
        $error_message = "Please enter a valid quantity.";
    } else {
        // Calculate new quantity
        $new_quantity = $type == 'add' 
                        ? $product['quantity'] + $quantity 
                        : $product['quantity'] - $quantity;
        
        // Check if removing more than available
        if ($type == 'remove' && $new_quantity < 0) {
            $error_message = "Cannot remove more than available stock.";
        } else {
            // Update product quantity
            $sql_update = "UPDATE products SET quantity = '$new_quantity' WHERE id = '$product_id'";
            
            if ($conn->query($sql_update) === TRUE) {
                // Record transaction
                $quantity_change = $type == 'add' ? $quantity : -$quantity;
                $transaction_type = $type == 'add' ? 'stock_in' : 'stock_out';
                
                $sql_transaction = "INSERT INTO inventory_transactions 
                                   (product_id, quantity_change, transaction_type, notes, date, user_id) 
                                   VALUES ('$product_id', '$quantity_change', '$transaction_type', '$notes', NOW(), '$user_id')";
                
                if ($conn->query($sql_transaction) === TRUE) {
                    header("Location: products.php");
                    exit();
                } else {
                    $error_message = "Error recording transaction: " . $conn->error;
                }
            } else {
                $error_message = "Error updating stock: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $type == 'add' ? 'Add' : 'Remove'; ?> Stock - Sari-Sari Store Inventory</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><?php echo $type == 'add' ? 'Add' : 'Remove'; ?> Stock</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $username; ?> (<?php echo $role; ?>)</span>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="product-info">
                <h2><?php echo $product['name']; ?></h2>
                <p><strong>Category:</strong> <?php echo $product['category_name']; ?></p>
                <p><strong>Current Stock:</strong> <?php echo $product['quantity']; ?> units</p>
                <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
            </div>
            
            <div class="form-container">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?product_id=$product_id&type=$type"); ?>">
                    <div class="form-group">
                        <label for="quantity">Quantity to <?php echo $type == 'add' ? 'Add' : 'Remove'; ?></label>
                        <input type="number" id="quantity" name="quantity" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn-save"><?php echo $type == 'add' ? 'Add' : 'Remove'; ?> Stock</button>
                        <a href="products.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
