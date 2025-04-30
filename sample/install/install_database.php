<?php
/**
 * Database Installation Script
 * 
 * This script creates the database and tables for the Sari-Sari Store application.
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'reptile_tsrfrs_store'; // Change this to your desired database name

// Connect to MySQL server without selecting a database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Read SQL file
$sql_file = file_get_contents(__DIR__ . '/../database/reptile_tsrfrs_store.sql');

// Split SQL file into individual statements
$sql_statements = explode(';', $sql_file);

// Execute each statement
$success = true;
foreach ($sql_statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        if ($conn->query($statement) !== TRUE) {
            echo "Error executing statement: " . $conn->error . "<br>";
            echo "Statement: " . $statement . "<br><br>";
            $success = false;
        }
    }
}

if ($success) {
    echo "Database installation completed successfully!<br>";
    echo "You can now <a href='../index.php'>login</a> with username: <strong>admin</strong> and password: <strong>admin123</strong>";
} else {
    echo "Database installation completed with errors. Please check the error messages above.";
}

$conn->close();
?>
