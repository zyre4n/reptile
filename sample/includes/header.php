
<?php
require_once __DIR__ . '/../config.php'; // Include the fixed config.php

// Define the getCurrentPage() function if not already defined
if (!function_exists('getCurrentPage')) {
    function getCurrentPage() {
        return basename($_SERVER['PHP_SELF']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($current_page == 'index.php' || $current_page == 'dashboard.php') ? 'Dashboard' : ucfirst(str_replace('.php', '', $current_page)); ?> - <?php echo $app_name; ?> Inventory</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --primary-color: #4a6741;
            --primary-hover: #3a5331;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Wrapper Layout */
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .content-wrapper {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
            transition: margin-left 0.3s;
        }
        
        /* Sidebar */
        .sidebar-container {
            position: relative;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: transform 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            transition: all 0.3s;
        }
        
        .sidebar-menu .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #fff;
        }
        
        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.8rem;
        }
        
        .sidebar-footer p {
            margin-bottom: 5px;
            opacity: 0.8;
        }
        
        .sidebar-toggler {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1050;
            display: none;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        /* Cards */
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        
        /* Dashboard Stats */
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        
        .stat-card .display-4 {
            font-weight: 600;
        }
        
        /* Tables */
        .table th {
            font-weight: 500;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(74, 103, 65, 0.05);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .content-wrapper {
                margin-left: 0;
            }
            
            .sidebar-toggler {
                display: block;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .content-wrapper.sidebar-open {
                margin-left: 250px;
            }
        }
        
        /* Custom Bootstrap Overrides */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        /* Badge styles */
        .badge.bg-success {
            background-color: var(--success-color) !important;
        }
        
        .badge.bg-warning {
            background-color: var(--warning-color) !important;
            color: #212529;
        }
        
        .badge.bg-info {
            background-color: var(--info-color) !important;
        }
        
        /* Progress bar */
        .progress-bar {
            background-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="wrapper">


    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

