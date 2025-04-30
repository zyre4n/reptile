<!-- filepath: c:\xampp\htdocs\maya's retail store\auth\forgot_password.php -->
<?php
session_start();
include '../config/database.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);

    // Check if email exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token in the database
        $sql_token = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $stmt_token = $conn->prepare($sql_token);
        $stmt_token->bind_param("sss", $reset_token, $expiry_time, $email);
        $stmt_token->execute();

        // Send reset link via email (mocked here)
        $reset_link = "http://localhost/maya's retail store/auth/reset_password.php?token=$reset_token";
        $success = "A password reset link has been sent to your email.";
        // In a real application, send the $reset_link via email.
    } else {
        $error = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style2.css">
</head>
</head>
<body>
    <div class="login-container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg">
            <h2 class="text-center mb-4">Forgot Password</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            </form>
            <div class="text-center mt-3">
                        <a href="../auth/login.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Back to Home
                        </a>
            </div>
        </div>
    </div>
</body>
</html>