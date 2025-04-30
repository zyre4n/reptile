<?php
session_start();
require_once '../config/database.php'; // Include database connection

$error = '';
$success = '';
$username = '';
$email = '';
$fullName = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['password_confirm'] ?? '');

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif (strlen($username) < 3 || strlen($username) > 64) {
        $error = "Username must be between 3 and 64 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $existingUser = $stmt->get_result()->fetch_assoc();

        if ($existingUser) {
            if ($existingUser['username'] === $username) {
                $error = "Username already taken. Please choose another one.";
            } else {
                $error = "Email already registered. Please use another email address.";
            }
        } else {
            // Create new user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // Default role for new registrations

            // Count total users to make the first user an admin
            $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch_assoc()['count'];

            if ($userCount == 0) {
                $role = 'admin'; // First user becomes an admin
            }

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("sssss", $username, $email, $passwordHash, $fullName, $role);

            if ($stmt->execute()) {
                $success = "Registration successful! You can now log in.";
                // Clear form data
                $username = '';
                $email = '';
                $fullName = '';
            } else {
                $error = "Error creating account. Please try again.";
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
    <title>Register - Sari-Sari Store Inventory</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4a6741;
            --primary-hover: #3a5331;
        }
        
        body {
            background: url('../assets/images/background.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        
        .auth-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .auth-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .auth-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .auth-header p {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 0;
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
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Maya Retail store</h2>
                <p>Inventory Management System</p>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <h4 class="text-center mb-4">Create New Account</h4>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" value="<?php echo htmlspecialchars($username); ?>" required>
                        <small class="form-text text-muted">Username must be at least 3 characters long</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($fullName); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required>
                        <small class="form-text text-muted">Password must be at least 6 characters long</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Confirm your password" required>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Register</button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login</a></p>
                    </div>
                </form>
                <div class="text-center mt-3">
                        <a href="../index.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Back to Home
                        </a>
                    </div>
            </div>
            <div class="card-footer text-center bg-light p-3">
                <small class="text-muted">© <?php echo date('Y'); ?> Sari-Sari Store Inventory Management System</small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>