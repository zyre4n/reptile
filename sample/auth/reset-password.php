<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';
$valid_token = false;
$user_id = null;

// Check if token is provided
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);
    
    // Check if token exists and is valid
    $sql = "SELECT pr.user_id, u.username 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.reset_token = '$token' 
            AND pr.token_expiry > NOW() 
            AND pr.is_used = 0";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $valid_token = true;
        $user_id = $row['user_id'];
        $username = $row['username'];
    } else {
        $error = "Invalid or expired password reset token.";
    }
} else {
    $error = "No reset token provided.";
}

// Process password reset form
if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = "Both password fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user's password
        $update_sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
        
        if ($conn->query($update_sql) === TRUE) {
            // Mark token as used
            $mark_used_sql = "UPDATE password_resets SET is_used = 1 WHERE user_id = $user_id AND reset_token = '$token'";
            $conn->query($mark_used_sql);
            
            $success = "Your password has been reset successfully! You can now log in with your new password.";
        } else {
            $error = "Error updating password: " . $conn->error;
        }
    }
}

// Set page title
$page_title = "Reset Password";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo get_config('store_name', 'Sari-Sari Store'); ?> Inventory</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .password-strength {
            height: 5px;
            margin-top: 5px;
            background-color: #e9ecef;
            border-radius: 3px;
            position: relative;
        }
        
        .password-strength-meter {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease-in-out;
        }
        
        .password-strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h2 class="text-primary"><?php echo get_config('store_name', 'Sari-Sari Store'); ?></h2>
                            <p class="text-muted">Reset Password</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                            
                            <?php if (!$valid_token): ?>
                                <div class="text-center mt-3">
                                    <a href="forgot-password.php" class="btn btn-primary">
                                        <i class="bi bi-arrow-left me-1"></i> Back to Forgot Password
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success; ?>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Go to Login
                                </a>
                            </div>
                        <?php elseif ($valid_token): ?>
                            <p class="mb-4">Please enter your new password below.</p>
                            
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?token=" . $token); ?>" id="resetForm">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength">
                                        <div class="password-strength-meter" id="strengthMeter"></div>
                                    </div>
                                    <div class="password-strength-text text-muted" id="strengthText"></div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>Reset Password
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Password visibility toggle
            const togglePassword = document.getElementById("togglePassword");
            const password = document.getElementById("password");
            
            if (togglePassword) {
                togglePassword.addEventListener("click", function() {
                    const type = password.getAttribute("type") === "password" ? "text" : "password";
                    password.setAttribute("type", type);
                    
                    // Toggle eye icon
                    this.querySelector("i").classList.toggle("bi-eye");
                    this.querySelector("i").classList.toggle("bi-eye-slash");
                });
            }
            
            // Password strength meter
            const strengthMeter = document.getElementById("strengthMeter");
            const strengthText = document.getElementById("strengthText");
            
            if (password && strengthMeter && strengthText) {
                password.addEventListener("input", function() {
                    const val = this.value;
                    
                    // Simple strength calculation
                    let strength = 0;
                    let text = "";
                    
                    if (val.length > 0) {
                        // Length check
                        if (val.length >= 8) strength += 1;
                        if (val.length >= 12) strength += 1;
                        
                        // Character type checks
                        if (/[a-z]/.test(val)) strength += 1;
                        if (/[A-Z]/.test(val)) strength += 1;
                        if (/[0-9]/.test(val)) strength += 1;
                        if (/[^a-zA-Z0-9]/.test(val)) strength += 1;
                    }
                    
                    // Update the strength meter
                    let width = "0%";
                    let color = "#e9ecef";
                    
                    switch (strength) {
                        case 0:
                            width = "0%";
                            color = "#e9ecef";
                            text = "";
                            break;
                        case 1:
                        case 2:
                            width = "25%";
                            color = "#dc3545"; // danger
                            text = "Very Weak";
                            break;
                        case 3:
                            width = "50%";
                            color = "#ffc107"; // warning
                            text = "Weak";
                            break;
                        case 4:
                            width = "75%";
                            color = "#fd7e14"; // orange
                            text = "Good";
                            break;
                        case 5:
                            width = "100%";
                            color = "#20c997"; // teal
                            text = "Strong";
                            break;
                        case 6:
                            width = "100%";
                            color = "#28a745"; // success
                            text = "Very Strong";
                            break;
                    }
                    
                    strengthMeter.style.width = width;
                    strengthMeter.style.backgroundColor = color;
                    strengthText.textContent = text;
                    strengthText.style.color = color;
                });
            }
            
            // Form validation
            const resetForm = document.getElementById("resetForm");
            const confirmPassword = document.getElementById("confirm_password");
            
            if (resetForm) {
                resetForm.addEventListener("submit", function(e) {
                    if (password.value !== confirmPassword.value) {
                        e.preventDefault();
                        alert("Passwords do not match!");
                    }
                });
            }
        });
    </script>
</body>
</html>
