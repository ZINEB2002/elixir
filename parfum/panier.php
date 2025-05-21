<?php
require_once 'config/config.php';

// Vérifier si l'utilisateur est connecté avant d'inclure le header
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/panier.php';
    redirect('/login.php');
}

require_once 'includes/header.php';

// Récupérer le panier de l'utilisateur
$stmt = $pdo->prepare("
    SELECT ci.*, p.name, p.price, p.image_url, p.stock_quantity, b.name as brand_name
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    JOIN brands b ON p.brand_id = b.id
    JOIN carts c ON ci.cart_id = c.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Calculer le total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<div class="container py-5">
    <h2 class="mb-4">Mon Panier</h2>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Votre panier est vide.
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Liste des produits -->
            <div class="col-md-8">
                <?php foreach ($cart_items as $item): ?>
                    <div class="card mb-3">
                        <div class="row g-0">
                            <div class="col-md-2">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     class="img-fluid rounded-start" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-md-10">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <h5 class="card-title mb-1">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </h5>
                                            <p class="card-text text-muted mb-0">
                                                <?php echo htmlspecialchars($item['brand_name']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control text-center" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" 
                                                       max="<?php echo $item['stock_quantity']; ?>"
                                                       onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value)">
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <p class="mb-0">Prix unitaire</p>
                                            <strong><?php echo number_format($item['price'], 2); ?> MDH</strong>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <p class="mb-0">Total</p>
                                            <strong><?php echo number_format($item['price'] * $item['quantity'], 2); ?> MDH</strong>
                                            <button class="btn btn-link text-danger p-0 mt-2" 
                                                    onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Résumé de la commande -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Résumé de la commande</h5>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sous-total</span>
                            <span><?php echo number_format($total, 2); ?> MDH</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Livraison</span>
                            <span>Gratuite</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total</strong>
                            <strong><?php echo number_format($total, 2); ?> MDH</strong>
                        </div>

                        <a href="checkout.php" class="btn btn-primary w-100">
                            Passer la commande
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(productId, newValue) {
    const input = document.querySelector(`input[onchange*="${productId}"]`);
    const maxValue = parseInt(input.max);
    
    if (newValue >= 1 && newValue <= maxValue) {
        fetch('api/cart/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: parseInt(newValue)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour le total pour ce produit
                const card = input.closest('.card');
                const totalElement = card.querySelector('.col-md-2.text-end strong');
                totalElement.textContent = data.item_total.toFixed(2) + ' MDH';

                // Mettre à jour le total général
                document.querySelectorAll('.d-flex.justify-content-between.mb-4 strong').forEach(element => {
                    element.textContent = data.cart_total.toFixed(2) + ' MDH';
                });
                // Mettre à jour le sous-total également
                document.querySelectorAll('.d-flex.justify-content-between.mb-2 span:last-child').forEach(element => {
                    if (element.textContent.includes('MDH')) {
                        element.textContent = data.cart_total.toFixed(2) + ' MDH';
                    }
                });
            } else {
                showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
                // Restaurer l'ancienne valeur en cas d'erreur
                input.value = input.defaultValue;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors de la mise à jour', 'error');
            // Restaurer l'ancienne valeur en cas d'erreur
            input.value = input.defaultValue;
        });
    } else {
        // Restaurer l'ancienne valeur si la nouvelle valeur est invalide
        input.value = input.defaultValue;
    }
}

function updateCartTotal() {
    let total = 0;
    document.querySelectorAll('.col-md-2.text-end strong').forEach(element => {
        const priceText = element.textContent;
        if (priceText.includes('MDH')) {
            total += parseFloat(priceText.replace('MDH', '').trim());
        }
    });
    
    // Mettre à jour tous les éléments affichant le total
    document.querySelectorAll('.d-flex.justify-content-between.mb-4 strong').forEach(element => {
        element.textContent = total.toFixed(2) + ' MDH';
    });
}

function removeFromCart(productId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
        return;
    }

    fetch('api/cart/remove.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'Erreur lors de la suppression', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la suppression', 'error');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?> 