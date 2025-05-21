<?php
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Vérifier si l'ID de la commande est fourni
if (!isset($_GET['id'])) {
    header('Location: profile.php');
    exit;
}

// Récupérer les détails de la commande
$stmt = $pdo->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email, u.phone, 
           u.address, u.city, u.postal_code, u.country
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([(int)$_GET['id'], $_SESSION['user_id']]);
$order = $stmt->fetch();

// Si la commande n'existe pas ou n'appartient pas à l'utilisateur
if (!$order) {
    header('Location: profile.php');
    exit;
}

// Récupérer les articles de la commande
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image_url,
           oi.price as unit_price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order['id']]);
$items = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Détails de la commande #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
                <a href="profile.php#orders" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Retour aux commandes
                </a>
            </div>

            <!-- Informations de la commande -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">Informations de la commande</h5>
                            <p class="mb-1">
                                <strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </p>
                            <p class="mb-1">
                                <strong>Statut :</strong>
                                <span class="badge bg-<?php 
                                    echo $order['status'] === 'completed' ? 'success' : 
                                        ($order['status'] === 'pending' ? 'warning' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </p>
                            <p class="mb-1">
                                <strong>Total :</strong> <?php echo number_format($order['total_amount'], 2); ?> MDH
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Adresse de livraison</h5>
                            <p class="mb-1"><?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['address'] ?? ''); ?></p>
                            <p class="mb-1">
                                <?php 
                                $postal_city = trim(($order['postal_code'] ?? '') . ' ' . ($order['city'] ?? ''));
                                echo htmlspecialchars($postal_city); 
                                ?>
                            </p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['country'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Articles de la commande -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Articles commandés</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['image_url']): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                         class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($item['unit_price'] ?? 0, 2); ?> MDH</td>
                                        <td><?php echo $item['quantity'] ?? 0; ?></td>
                                        <td><?php echo number_format(($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0), 2); ?> MDH</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                                    <td><strong><?php echo number_format($order['total_amount'], 2); ?> MDH</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>