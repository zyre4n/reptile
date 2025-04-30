<?php
session_start();
include '../config/database.php';

// Log the logout action if user is an admin
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Log admin logout
    $log_details = "Admin logout from IP: $ip_address";
    $sql_log = "INSERT INTO audit_log (user_id, action, entity_type, details, ip_address) 
               VALUES ('$user_id', 'logout', 'admin', '$log_details', '$ip_address')";
    $conn->query($sql_log);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete the remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to admin login page
header("Location: ../admin-login.php");
exit();
?>
