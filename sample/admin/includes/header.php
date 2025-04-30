<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo get_config('store_name', 'Sari-Sari Store'); ?> Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-header {
            background-color: #dc3545;
            color: white;
        }
        
        .admin-sidebar .nav-link.active {
            background-color: rgba(220, 53, 69, 0.2);
            border-left: 4px solid #dc3545;
        }
        
        .admin-sidebar .nav-link:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }
    </style>
</head>
<body>
