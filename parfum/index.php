<?php
require_once 'includes/header.php';

// Récupération des marques
$stmt = $pdo->query("SELECT * FROM brands ORDER BY name");
$brands = $stmt->fetchAll();

// Récupération des catégories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <div class="hero-background"></div>
    <div class="container position-relative">
        <div class="row min-vh-75 align-items-center">
            <div class="col-lg-6">
                <div class="hero-content text-lg-start text-center">
                    <h1 class="display-3 fw-bold mb-4 text-dark">Découvrez l'Art du Parfum</h1>
                    <p class="lead mb-5 text-dark opacity-75">Une collection exclusive de parfums de luxe pour sublimer votre personnalité</p>
                    <div class="d-flex gap-3 justify-content-lg-start justify-content-center">
                        <a href="catalogue.php" class="btn btn-dark btn-lg px-5 py-3 rounded-pill">
                            Explorer la Collection
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-image position-relative">
                    <img src="assets/css/télécharger.jpeg" alt="Parfum de luxe" class="img-fluid hero-img">
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 6rem 0;
    position: relative;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('assets/images/pattern.png') repeat;
    opacity: 0.1;
    animation: slide 20s linear infinite;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-image {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}

.hero-img {
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    max-width: 85%;
    object-fit: cover;
    border: 8px solid rgba(255, 255, 255, 0.9);
    transition: all 0.3s ease;
}



@keyframes slide {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: 100% 100%;
    }
}

.min-vh-75 {
    min-height: 75vh;
}
</style>

<!-- Brands Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Nos Marques Prestigieuses</h2>
        <div class="row">
            <?php foreach ($brands as $brand): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($brand['logo_url']): ?>
                            <img src="<?php echo htmlspecialchars($brand['logo_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($brand['name']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($brand['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($brand['description']); ?></p>
                            <a href="<?php echo SITE_URL; ?>/catalogue.php?brand=<?php echo $brand['id']; ?>" class="btn btn-outline-primary">
                                Voir les produits
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo SITE_URL; ?>/marques.php" class="btn btn-primary">Voir toutes les marques</a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Explorez par Catégorie</h2>
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                            <a href="<?php echo SITE_URL; ?>/catalogue.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-primary">
                                Découvrir
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo SITE_URL; ?>/categories.php" class="btn btn-primary">Voir toutes les catégories</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 