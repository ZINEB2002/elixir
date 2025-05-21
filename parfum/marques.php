<?php
require_once 'includes/header.php';

// Récupérer toutes les marques avec le nombre de produits
$stmt = $pdo->query("
    SELECT b.*, COUNT(p.id) as product_count
    FROM brands b
    LEFT JOIN products p ON b.id = p.brand_id
    GROUP BY b.id
    ORDER BY b.name ASC
");
$brands = $stmt->fetchAll();
?>

<div class="container py-5">
    <h1 class="mb-4">Nos Marques</h1>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($brands as $brand): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <?php if ($brand['logo_url']): ?>
                        <img src="<?php echo htmlspecialchars($brand['logo_url']); ?>" 
                             class="card-img-top p-3" 
                             alt="<?php echo htmlspecialchars($brand['name']); ?>"
                             style="height: 200px; object-fit: contain;">
                    <?php else: ?>
                        <div class="card-img-top p-3 d-flex align-items-center justify-content-center bg-light" 
                             style="height: 200px;">
                            <i class="fas fa-spray-can-sparkles fa-4x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($brand['name']); ?></h5>
                        <?php if (!empty($brand['description'])): ?>
                            <p class="card-text"><?php echo htmlspecialchars(substr($brand['description'], 0, 100)) . '...'; ?></p>
                        <?php endif; ?>
                        <p class="card-text">
                            <small class="text-muted"><?php echo $brand['product_count']; ?> produits</small>
                        </p>
                        <a href="<?php echo SITE_URL; ?>/catalogue.php?brand=<?php echo $brand['id']; ?>" class="btn btn-primary">
                            Voir les produits
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 