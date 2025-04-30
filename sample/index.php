<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Function to get configuration values (mock implementation for demonstration)
function get_config($key, $default = '') {
    $config = [
        'store_name' => 'MAYA RETAIL STORE',
        'store_email' => 'info@sarisaristore.com',
        'store_contact' => '+63 912 345 6789',
        'store_address' => '123 Main Street, Manila, Philippines',
    ];
    return $config[$key] ?? $default;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(get_config('store_name', 'Sari-Sari Store')); ?> Inventory System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <div class="logo">
                <h1><?php echo htmlspecialchars(get_config('store_name', 'MAYA RETAIL STORE')); ?></h1>
                <p>Inventory Management System</p>
            </div>
            <nav>
                <ul>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="auth/login.php" class="btn-login">User Login</a></li>
                    <li><a href="admin/login.php" class="btn-login admin">Admin Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Manage Your Store Inventory with Ease</h1>
                <p>A simple and efficient inventory management system designed specifically for sari-sari stores.</p>
                <div class="hero-buttons">
                    <a href="auth/login.php" class="btn-primary">User Login</a>
                    <a href="admin/login.php" class="btn-secondary">Admin Login</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/images/image.png" alt="Sari-Sari Store Illustration">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2>Key Features</h2>
            <div class="features-grid">
                <!-- Feature Cards -->
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-box-seam"></i></div>
                    <h3>Product Management</h3>
                    <p>Easily add, edit, and track all your store products in one place.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-graph-up"></i></div>
                    <h3>Inventory Tracking</h3>
                    <p>Monitor stock levels and get alerts when items are running low.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-tags"></i></div>
                    <h3>Category Organization</h3>
                    <p>Organize products by categories for better management.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-people"></i></div>
                    <h3>User Management</h3>
                    <p>Control access with different user roles for store owners and staff.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-phone"></i></div>
                    <h3>Mobile Friendly</h3>
                    <p>Access your inventory system from any device, anywhere.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-shield-lock"></i></div>
                    <h3>Secure Access</h3>
                    <p>Keep your store data safe with secure login and user permissions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <h2>About Our System</h2>
                <p>Our <?php echo htmlspecialchars(get_config('store_name', 'Sari-Sari Store')); ?> Inventory Management System is designed specifically for small neighborhood convenience stores in the Philippines. We understand the unique challenges of managing a sari-sari store and have created a simple yet powerful solution to help store owners track their inventory, monitor stock levels, and make informed business decisions.</p>
                <p>With our system, you can say goodbye to manual inventory tracking and hello to efficient store management. Whether you're a small family-owned store or managing multiple locations, our system scales to meet your needs.</p>
            </div>
            <div class="about-image">
                <img src="assets/images/about.png" alt="About Illustration">
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2>Contact Us</h2>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Get in Touch</h3>
                    <p>Have questions about our inventory system? We're here to help!</p>
                    <div class="info-item">
                        <span class="icon"><i class="bi bi-envelope"></i></span>
                        <p><?php echo htmlspecialchars(get_config('store_email', 'info@sarisaristore.com')); ?></p>
                    </div>
                    <div class="info-item">
                        <span class="icon"><i class="bi bi-telephone"></i></span>
                        <p><?php echo htmlspecialchars(get_config('store_contact', '+63 912 345 6789')); ?></p>
                    </div>
                    <div class="info-item">
                        <span class="icon"><i class="bi bi-geo-alt"></i></span>
                        <p><?php echo htmlspecialchars(get_config('store_address', '123 Main Street, Manila, Philippines')); ?></p>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>Send a Message</h3>
                    <form action="process-contact.php" method="post">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(get_config('store_name', 'Sari-Sari Store')); ?> Inventory System. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Help Center</a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>