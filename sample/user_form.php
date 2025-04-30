<?php
session_start();
include 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Initialize variables
$id = '';
$edit_username = '';
$full_name = '';
$edit_role = '';
$is_edit = false;

// Check if editing existing user
if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $edit_username = $row['username'];
        $full_name = $row['full_name'];
        $edit_role = $row['role'];
        $is_edit = true;
    } else {
        header("Location: users.php");
        exit();
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $edit_username = $conn->real_escape_string($_POST['username']);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $edit_role = $conn->real_escape_string($_POST['role']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Check if username already exists
    $check_sql = "SELECT id FROM users WHERE username = '$edit_username' AND id != '$id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $error_message = "Username already exists. Please choose another.";
    } else {
        if ($is_edit) {
            // Update existing user
            if (!empty($password)) {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET 
                        username = '$edit_username', 
                        full_name = '$full_name', 
                        role = '$edit_role',
                        password = '$hashed_password'
                        WHERE id = '$id'";
            } else {
                // Update without changing password
                $sql = "UPDATE users SET 
                        username = '$edit_username', 
                        full_name = '$full_name', 
                        role = '$edit_role'
                        WHERE id = '$id'";
            }
                    
            if ($conn->query($sql) === TRUE) {
                header("Location: users.php");
                exit();
            } else {
                $error_message = "Error updating user: " . $conn->error;
            }
        } else {
            // Create new user
            if (empty($password)) {
                $error_message = "Password is required for new users.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password, full_name, role) 
                        VALUES ('$edit_username', '$hashed_password', '$full_name', '$edit_role')";
                        
                if ($conn->query($sql) === TRUE) {
                    header("Location: users.php");
                    exit();
                } else {
                    $error_message = "Error creating user: " . $conn->error;
                }
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
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> User - Sari-Sari Store Inventory</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><?php echo $is_edit ? 'Edit' : 'Add'; ?> User</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $username; ?> (<?php echo $role; ?>)</span>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($is_edit ? "?id=$id" : "")); ?>">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo $edit_username; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo $full_name; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="admin" <?php echo $edit_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="user" <?php echo $edit_role == 'user' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password <?php echo $is_edit ? '(Leave blank to keep current)' : ''; ?></label>
                        <input type="password" id="password" name="password" <?php echo $is_edit ? '' : 'required'; ?>>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn-save"><?php echo $is_edit ? 'Update' : 'Save'; ?> User</button>
                        <a href="users.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
