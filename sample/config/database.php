<?php
// Database connection configuration
$host = 'localhost';
$db_name = 'reptile_tsrfrs_db'; // Database name
$username = 'root'; // Default username for XAMPP
$password = ''; // Default password for XAMPP
$charset = 'utf8mb4';

// Create connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
if (!$conn->set_charset($charset)) {
    die("Error loading character set $charset: " . $conn->error);
}
?>