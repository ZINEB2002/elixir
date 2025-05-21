<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour gérer la redirection du panier
function handleCartRedirect() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = SITE_URL . '/panier.php';
        redirect('/login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <i class="fas fa-spray-can-sparkles"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/catalogue.php">Catalogue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/marques.php">Marques</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/categories.php">Catégories</a>
                    </li>
                </ul>
                <form class="d-flex me-3" action="<?php echo SITE_URL; ?>/catalogue.php" method="GET">
                    <input class="form-control me-2 bg-transparent border-0 border-bottom border-dark rounded-0" 
                           type="search" 
                           name="search" 
                           placeholder="Rechercher un parfum..."
                           style="box-shadow: none;">
                    <button class="btn btn-link text-dark p-0" type="submit">
                        <i class="fas fa-search fa-lg"></i>
                    </button>
                </form>
                <div class="d-flex align-items-center">
                    <a href="<?php echo SITE_URL; ?>/panier.php" class="btn btn-link text-dark p-0 me-2 position-relative">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo SITE_URL; ?>/profile.php" class="btn btn-link text-dark p-0 me-2" title="Profil">
                            <i class="fas fa-user-circle fa-lg"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-link text-dark p-0" title="Connexion">
                            <i class="fas fa-sign-in-alt fa-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Notifications -->
    <div id="notifications" class="position-fixed top-0 end-0 p-3" style="z-index: 1050"></div>

    <main class="container py-4">
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du menu burger
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        
        if (navbarToggler && navbarCollapse) {
            navbarToggler.addEventListener('click', function() {
                navbarCollapse.classList.toggle('show');
            });

            // Fermer le menu quand on clique sur un lien
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    navbarCollapse.classList.remove('show');
                });
            });

            // Fermer le menu quand on clique en dehors
            document.addEventListener('click', function(event) {
                if (!navbarCollapse.contains(event.target) && !navbarToggler.contains(event.target)) {
                    navbarCollapse.classList.remove('show');
                }
            });
        }

        // Fonction pour gérer le clic sur le panier
        function handleCartClick(event) {
            <?php if (!isLoggedIn()): ?>
            event.preventDefault();
            window.location.href = '<?php echo SITE_URL; ?>/login.php?redirect=panier';
            return false;
            <?php endif; ?>
            return true;
        }

        // Fonction pour mettre à jour le nombre d'articles dans le panier
        function updateCartCount() {
            fetch('/api/cart/count.php')
                .then(response => response.json())
                .then(data => {
                    const cartCount = document.getElementById('cart-count');
                    cartCount.textContent = data.count;
                    if (data.count > 0) {
                        cartCount.style.display = 'block';
                    } else {
                        cartCount.style.display = 'none';
                    }
                })
                .catch(error => console.error('Erreur:', error));
        }

        // Mettre à jour le compteur au chargement de la page
        updateCartCount();
    });
    </script>
</body>
</html> 