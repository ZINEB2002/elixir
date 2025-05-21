<?php
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('/login.php');
}

// Récupérer l'ID de la commande
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$order_id) {
    redirect('/commandes.php');
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
    redirect('/commandes.php');
}

// Récupérer les articles de la commande
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url, p.description
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/commandes.php">Mes commandes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Commande #<?php echo $order_id; ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Détails de la commande -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Commande #<?php echo $order_id; ?></h5>
                        <span class="badge bg-<?php 
                            echo [
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger'
                            ][$order['status']] ?? 'secondary';
                        ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informations de livraison</h6>
                            <p class="mb-1"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                            <p class="mb-1">
                                <?php echo htmlspecialchars($order['shipping_postal_code'] . ' ' . $order['shipping_city']); ?>
                            </p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['shipping_country']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Informations de commande</h6>
                            <p class="mb-1">Date : <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
                            <p class="mb-1">Email : <?php echo htmlspecialchars($order['email']); ?></p>
                            <?php if ($order['notes']): ?>
                                <p class="mb-1">Notes : <?php echo htmlspecialchars($order['notes']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h6 class="mb-3">Articles commandés</h6>
                    <?php foreach ($order_items as $item): ?>
                        <div class="card mb-3">
                            <div class="row g-0">
                                <div class="col-md-2">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         class="img-fluid rounded-start" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="col-md-10">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                                <p class="card-text text-muted">
                                                    <?php echo htmlspecialchars(substr($item['description'], 0, 100)) . '...'; ?>
                                                </p>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <p class="mb-0">Quantité</p>
                                                <strong><?php echo $item['quantity']; ?></strong>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <p class="mb-0">Prix unitaire</p>
                                                <strong><?php echo number_format($item['price'], 2); ?> €</strong>
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <p class="mb-0">Total</p>
                                                <strong><?php echo number_format($item['price'] * $item['quantity'], 2); ?> €</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Résumé et actions -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Résumé de la commande</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total</span>
                        <span><?php echo number_format($order['total_amount'], 2); ?> €</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Livraison</span>
                        <span>Gratuite</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total</strong>
                        <strong><?php echo number_format($order['total_amount'], 2); ?> €</strong>
                    </div>

                    <?php if ($order['status'] === 'pending'): ?>
                        <button class="btn btn-danger w-100 mb-3" onclick="cancelOrder(<?php echo $order_id; ?>)">
                            <i class="fas fa-times"></i> Annuler la commande
                        </button>
                    <?php endif; ?>

                    <button class="btn btn-outline-primary w-100" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimer la commande
                    </button>
                </div>
            </div>

            <?php if ($order['status'] === 'shipped'): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Suivi de livraison</h5>
                        <div class="tracking-info">
                            <p class="mb-2">
                                <strong>Transporteur :</strong> <?php echo htmlspecialchars($order['shipping_carrier']); ?>
                            </p>
                            <p class="mb-2">
                                <strong>Numéro de suivi :</strong> 
                                <?php echo htmlspecialchars($order['tracking_number']); ?>
                            </p>
                            <a href="<?php echo htmlspecialchars($order['tracking_url']); ?>" 
                               class="btn btn-outline-primary w-100" 
                               target="_blank">
                                Suivre ma commande
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function cancelOrder(orderId) {
    if (!confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) {
        return;
    }

    fetch('/api/orders/cancel.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Commande annulée avec succès', 'success');
            setTimeout(() => location.href = '/commandes.php', 1500);
        } else {
            showNotification(data.message || 'Erreur lors de l\'annulation', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'annulation', 'error');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?> 