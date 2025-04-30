<?php
// Start session
session_start();

$error = '';
$success = '';

// Check if verification code is provided
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $verification_code = $conn->real_escape_string($_GET['code']);
    
    // Find user with this verification code
    $sql = "SELECT id, username FROM users WHERE verification_code = '$verification_code' AND is_verified = 0";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Update user as verified
        $update_sql = "UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = " . $user['id'];
        
        if ($conn->query($update_sql) === TRUE) {
            $success = "Your account has been verified successfully! You can now log in.";
        } else {
            $error = "Error updating verification status: " . $conn->error;
        }
    } else {
        $error = "Invalid verification code or account already verified.";
    }
} else {
    $error = "No verification code provided.";
}

// Set page title
$page_title = "Verify Account";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - <?php echo get_config('store_name', 'Sari-Sari Store'); ?> Inventory</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4 text-center">
                        <h2 class="text-primary mb-4"><?php echo get_config('store_name', 'Sari-Sari Store'); ?></h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo $success; ?>
                            </div>
                            
                            <div class="mt-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">Account Verified!</h4>
                                <p>Your email has been verified successfully.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="login.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Proceed to Login
                            </a>
                        </div>
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
</body>
</html>
