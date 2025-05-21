<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Traitement des actions
if (isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'update_status':
                if (isset($_POST['order_id']) && isset($_POST['status'])) {
                    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                    $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
                    $_SESSION['success'] = "Le statut de la commande a été mis à jour.";
                }
                break;
                
            case 'delete':
                if (isset($_POST['order_id'])) {
                    // Supprimer d'abord les détails de la commande
                    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                    $stmt->execute([(int)$_POST['order_id']]);
                    
                    // Puis supprimer la commande
                    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                    $stmt->execute([(int)$_POST['order_id']]);
                    
                    $_SESSION['success'] = "La commande a été supprimée avec succès.";
                }
                break;
        }
        
        header('Location: orders.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Une erreur est survenue lors du traitement de la commande.";
        header('Location: orders.php');
        exit;
    }
}

// Récupération des commandes avec les détails
$stmt = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.email,
           COUNT(oi.id) as item_count,
           SUM(oi.quantity * oi.price) as total_amount
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gestion des Commandes</h1>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
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
                                <td><?php echo $order['id']; ?></td>
                                <td>
                                    <?php 
                                    $customerName = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
                                    echo htmlspecialchars($customerName ?: 'Client inconnu'); 
                                    ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($order['email'] ?? 'Email non disponible'); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>En cours</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Expédiée</option>
                                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Livrée</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo $order['item_count']; ?> article(s)</td>
                                <td><?php echo number_format($order['total_amount'], 2); ?> MDH</td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal<?php echo $order['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <!-- Modal de détails de la commande -->
                                    <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Détails de la commande #<?php echo $order['id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    $stmt = $pdo->prepare("
                                                        SELECT oi.*, p.name as product_name, p.image_url
                                                        FROM order_items oi
                                                        LEFT JOIN products p ON oi.product_id = p.id
                                                        WHERE oi.order_id = ?
                                                    ");
                                                    $stmt->execute([$order['id']]);
                                                    $items = $stmt->fetchAll();
                                                    ?>
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Produit</th>
                                                                    <th>Quantité</th>
                                                                    <th>Prix unitaire</th>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($items as $item): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <?php if ($item['image_url']): ?>
                                                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                                                     alt="<?php echo htmlspecialchars($item['product_name'] ?? 'Produit inconnu'); ?>"
                                                                                     class="img-thumbnail"
                                                                                     style="max-width: 50px;">
                                                                            <?php endif; ?>
                                                                            <?php echo htmlspecialchars($item['product_name'] ?? 'Produit inconnu'); ?>
                                                                        </td>
                                                                        <td><?php echo $item['quantity']; ?></td>
                                                                        <td><?php echo number_format($item['price'], 2); ?> MDH</td>
                                                                        <td><?php echo number_format($item['quantity'] * $item['price'], 2); ?> MDH</td>
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

                                    <!-- Modal de confirmation de suppression -->
                                    <div class="modal fade" id="deleteModal<?php echo $order['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmer la suppression</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Êtes-vous sûr de vouloir supprimer la commande #<?php echo $order['id']; ?> ?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <form method="POST">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 