<?php
require_once 'includes/header.php';

// Récupérer l'ID du produit
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
    redirect('/catalogue.php');
}

// Récupérer les informations du produit
$stmt = $pdo->prepare("
    SELECT p.*, b.name as brand_name, b.description as brand_description, 
           c.name as category_name, c.description as category_description
    FROM products p
    JOIN brands b ON p.brand_id = b.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/catalogue.php');
}

// Récupérer les produits similaires
$stmt = $pdo->prepare("
    SELECT p.*, b.name as brand_name
    FROM products p
    JOIN brands b ON p.brand_id = b.id
    WHERE p.category_id = ? AND p.id != ? AND p.stock_quantity > 0
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product_id]);
$similar_products = $stmt->fetchAll();
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/catalogue.php">Catalogue</a></li>
            <li class="breadcrumb-item"><a href="/categorie.php?id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Image du produit -->
        <div class="col-md-6 mb-4">
            <div class="product-image-container">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     class="img-fluid rounded" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
        </div>

        <!-- Informations du produit -->
        <div class="col-md-6">
            <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="mb-3">
                <span class="badge bg-primary"><?php echo htmlspecialchars($product['brand_name']); ?></span>
                <span class="badge bg-secondary"><?php echo htmlspecialchars($product['category_name']); ?></span>
            </div>

            <p class="lead mb-4"><?php echo htmlspecialchars($product['description']); ?></p>

            <div class="mb-4">
                <h3 class="text-primary"><?php echo number_format($product['price'], 2); ?> €</h3>
                <?php if ($product['stock_quantity'] > 0): ?>
                    <span class="text-success">
                        <i class="fas fa-check-circle"></i> En stock
                    </span>
                <?php else: ?>
                    <span class="text-danger">
                        <i class="fas fa-times-circle"></i> Rupture de stock
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($product['stock_quantity'] > 0): ?>
                <div class="mb-4">
                    <div class="input-group" style="max-width: 200px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(-1)">-</button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(1)">+</button>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn btn-primary btn-lg mb-4 add-to-cart" 
                        data-product-id="<?php echo $product['id']; ?>">
                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                </button>
                <?php else: ?>
                <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary btn-lg mb-4">
                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                </a>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Informations supplémentaires -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informations produit</h5>
                    <ul class="list-unstyled">
                        <li><strong>Marque :</strong> <?php echo htmlspecialchars($product['brand_name']); ?></li>
                        <li><strong>Catégorie :</strong> <?php echo htmlspecialchars($product['category_name']); ?></li>
                        <li><strong>Stock disponible :</strong> <?php echo $product['stock_quantity']; ?> unités</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits similaires -->
    <?php if (!empty($similar_products)): ?>
        <section class="mt-5">
            <h3 class="mb-4">Produits similaires</h3>
            <div class="row">
                <?php foreach ($similar_products as $similar): ?>
                    <div class="col-md-3">
                        <div class="card product-card">
                            <img src="<?php echo htmlspecialchars($similar['image_url']); ?>" 
                                 class="card-img-top product-image" 
                                 alt="<?php echo htmlspecialchars($similar['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($similar['name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($similar['brand_name']); ?></p>
                                <p class="product-price"><?php echo number_format($similar['price'], 2); ?> €</p>
                                <a href="produit.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline-primary">
                                    Voir le produit
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
function updateQuantity(change) {
    const input = document.getElementById('quantity');
    const newValue = parseInt(input.value) + change;
    const max = parseInt(input.max);
    
    if (newValue >= 1 && newValue <= max) {
        input.value = newValue;
    }
}

// Mettre à jour le bouton d'ajout au panier
document.querySelector('.add-to-cart').addEventListener('click', function() {
    const quantity = document.getElementById('quantity').value;
    const productId = this.dataset.productId;
    
    fetch('/parfum/api/cart/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Produit ajouté au panier', 'success');
            updateCartCount();
        } else {
            // Vérifier si l'utilisateur doit être redirigé vers la page de connexion
            if (data.redirect_to_login && data.redirect_url) {
                // Redirection immédiate vers la page de connexion sans message
                window.location.href = data.redirect_url;
            } else {
                showNotification(data.message || 'Erreur lors de l\'ajout au panier', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'ajout au panier', 'error');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 