<?php
session_start();
include '../config/database.php';
include '../config/app_config.php';

echo '<pre>';
print_r($_SESSION);
echo '</pre>';

// Check if user is already logged in
if (isset($SESSION['user_id']) && isset($SESSION['role']) && $SESSION['role'] == 'user') {
    header("Location: ../dashboard.php");
    exit();
}

$error = '';

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // Check user credentials
    $sql = "SELECT id, username, password, role, is_active FROM users WHERE username = ? AND role = 'user'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (!$row['is_active']) {
            $error = "Your account has been deactivated. Please contact the administrator.";
        } elseif (password_verify($password, $row['password'])) {
            // Successful login
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // Insert session into user_sessions table
            $user_id = $row['id'];
            $session_token = bin2hex(random_bytes(32));
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));

            $sql = "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("issss", $user_id, $session_token, $ip_address, $user_agent, $expires_at);
                $stmt->execute();

                // Set cookie with session token
                setcookie('session_token', $session_token, time() + 86400, "/", "", false, true);
            } else {
                $error = "Failed to create session.";
    }
            // Handle "Remember Me" functionality
            if ($remember_me) {
                $session_token = bin2hex(random_bytes(32));
                $expiry_days = 30; // Remember for 30 days
                $insert_session = "INSERT INTO user_sessions (user_id, session_token, expires_at) 
                                   VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))";
                $stmt = $conn->prepare($insert_session);
                $stmt->bind_param("isi", $row['id'], $session_token, $expiry_days);
                $stmt->execute();

                // Set cookie with session token
                setcookie('remember_token', $session_token, time() + (86400 * $expiry_days), "/", "", false, true);
            }

            // Redirect to dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style2.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>User Login</h2>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">Remember Me</label>
                    </div>
                    <button href="../dashboard.php" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="../auth/forgot-password.php" class="text-decoration-none">Forgot Password?</a> | 
                    <a href="../auth/signup.php" class="text-decoration-none">Sign Up</a>
                </div>
                <div class="mt-3 text-center">
                    <a href="../admin/login.php" class="text-decoration-none">Admin Login</a>
                </form>

                <div class="text-center mt-3">
                        <a href="../index.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Back to Home
                        </a>
                    </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>