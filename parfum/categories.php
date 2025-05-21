<?php
require_once 'includes/header.php';

// Récupérer toutes les catégories avec le nombre de produits
$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();
?>

<div class="container py-5">
    <h1 class="mb-4">Nos Catégories</h1>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($categories as $category): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="category-icon me-3">
                                <?php
                                $icon = match(strtolower($category['name'])) {
                                    'parfums femmes' => 'fa-venus',
                                    'parfums hommes' => 'fa-mars',
                                    'parfums unisexes' => 'fa-venus-mars',
                                    default => 'fa-spray-can-sparkles'
                                };
                                ?>
                                <i class="fas <?php echo $icon; ?> fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($category['name']); ?></h5>
                        </div>
                        
                        <?php if (!empty($category['description'])): ?>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars(substr($category['description'], 0, 100)) . '...'; ?>
                            </p>
                        <?php endif; ?>
                        
                        <p class="card-text">
                            <small class="text-muted">
                                <?php echo $category['product_count']; ?> produits
                            </small>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="<?php echo SITE_URL; ?>/catalogue.php?category=<?php echo $category['id']; ?>" class="btn btn-primary w-100">
                            Voir les produits
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.category-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    border-radius: 50%;
}
</style>

<?php require_once 'includes/footer.php'; ?> 