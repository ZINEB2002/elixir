<?php
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('/login.php');
}

// Récupérer l'ID de la commande
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$order_id) {
    redirect('/');
}

// Récupérer les informations de la commande
$stmt = $pdo->prepare("
    SELECT o.*, u.email, u.first_name, u.last_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('/');
}

// Récupérer les articles de la commande
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
        <h2 class="mt-3">Merci pour votre commande !</h2>
        <p class="lead">Votre commande #<?php echo $order_id; ?> a bien été enregistrée.</p>
    </div>

    <div class="row">
        <!-- Détails de la commande -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Détails de la commande</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informations de livraison</h6>
                            <p class="mb-1"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['shipping_address'] ?? ''); ?></p>
                            <p class="mb-1">
                                <?php 
                                $postal_city = trim(($order['shipping_postal_code'] ?? '') . ' ' . ($order['shipping_city'] ?? ''));
                                echo htmlspecialchars($postal_city); 
                                ?>
                            </p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['shipping_country'] ?? ''); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['shipping_phone'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Informations de commande</h6>
                            <p class="mb-1">Numéro de commande : #<?php echo $order_id; ?></p>
                            <p class="mb-1">Date : <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
                            <p class="mb-1">Statut : <?php echo ucfirst($order['status']); ?></p>
                            <p class="mb-1">Total : <?php echo number_format($order['total_amount'], 2); ?> €</p>
                        </div>
                    </div>

                    <h6 class="mb-3">Articles commandés</h6>
                    <?php foreach ($order_items as $item): ?>
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 class="rounded" 
                                 style="width: 64px; height: 64px; object-fit: cover;"
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">Quantité : <?php echo $item['quantity']; ?></small>
                            </div>
                            <div class="text-end">
                                <strong><?php echo number_format($item['price'] * $item['quantity'], 2); ?> €</strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <a href="catalogue.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i> Continuer mes achats
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 