<?php
require_once 'includes/functions.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
            padding: 20px;
        }
        .user-info {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar d-flex flex-column">
        <div class="navbar-brand">
            <i class="fas fa-users"></i> CRM System
        </div>
        
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'leads.php' ? 'active' : ''; ?>" href="leads.php">
                <i class="fas fa-user-plus"></i> Leads
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                <i class="fas fa-users"></i> Customers
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tasks.php' ? 'active' : ''; ?>" href="tasks.php">
                <i class="fas fa-tasks"></i> Tasks
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'notes.php' ? 'active' : ''; ?>" href="notes.php">
                <i class="fas fa-sticky-note"></i> Notes
            </a>
            <?php if (isAdmin()): ?>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>" href="admin.php">
                <i class="fas fa-cog"></i> Admin Panel
            </a>
            <?php endif; ?>
        </nav>
        
        <div class="user-info">
            <div class="mb-2">
                <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
            </div>
            <div class="mb-2">
                <small><i class="fas fa-shield-alt"></i> <?php echo ucfirst($_SESSION['user_role']); ?></small>
            </div>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="main-content">
        <button class="btn btn-primary d-md-none mb-3" type="button" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i> Menu
        </button>
