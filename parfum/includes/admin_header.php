<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    <style>
        .sidebar {
            width: 250px;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            transition: all 0.3s;
        }
        
        .sidebar.collapsed {
            margin-left: -250px;
        }
        
        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.show {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
        
        .navbar {
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .content-wrapper {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar bg-dark text-white">
            <div class="sidebar-header p-3">
                <h5 class="mb-0">
                    <i class="fas fa-cog"></i> Administration
                </h5>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?php echo SITE_URL; ?>/admin">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?php echo SITE_URL; ?>/admin/products.php">
                        <i class="fas fa-box"></i> Produits
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?php echo SITE_URL; ?>/admin/brands.php">
                        <i class="fas fa-trademark"></i> Marques
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?php echo SITE_URL; ?>/admin/categories.php">
                        <i class="fas fa-tags"></i> Catégories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?php echo SITE_URL; ?>/admin/orders.php">
                        <i class="fas fa-shopping-cart"></i> Commandes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?php echo SITE_URL; ?>/admin/users.php">
                        <i class="fas fa-users"></i> Utilisateurs
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <!-- Top navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-link" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="d-flex align-items-center">
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-primary me-2" target="_blank">
                            <i class="fas fa-home"></i> Voir le site
                        </a>
                        <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-outline-danger">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Page content -->
            <div class="content-wrapper"> 