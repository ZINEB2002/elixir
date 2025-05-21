<?php
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('/login.php');
}

// Récupérer les commandes de l'utilisateur
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as total_items,
           GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<div class="container py-5">
    <h2 class="mb-4">Mes commandes</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            Vous n'avez pas encore passé de commande. 
            <a href="/catalogue.php">Découvrir nos produits</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Articles</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <?php
                                $status_class = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'shipped' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger'
                                ][$order['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo $order['total_items']; ?> article(s)
                                </small>
                                <br>
                                <small>
                                    <?php echo htmlspecialchars($order['product_names']); ?>
                                </small>
                            </td>
                            <td><?php echo number_format($order['total_amount'], 2); ?> €</td>
                            <td>
                                <a href="/commande.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Détails
                                </a>
                                <?php if ($order['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
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
            setTimeout(() => location.reload(), 1500);
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