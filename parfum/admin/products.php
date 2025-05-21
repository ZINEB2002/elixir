<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Traitement de la suppression
if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    try {
        // Récupérer l'image avant la suppression
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        
        // Supprimer l'image si elle existe
        if ($product && $product['image_url'] && file_exists('../' . $product['image_url'])) {
            unlink('../' . $product['image_url']);
        }
        
        header('Location: products.php?success=deleted');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression du produit.";
        header('Location: products.php');
        exit;
    }
}

// Récupération des produits avec leurs catégories et marques
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, b.name as brand_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gestion des Produits</h1>
        <a href="product_form.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un produit
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php
            switch ($_GET['success']) {
                case 'created':
                    echo "Le produit a été créé avec succès.";
                    break;
                case 'updated':
                    echo "Le produit a été mis à jour avec succès.";
                    break;
                case 'deleted':
                    echo "Le produit a été supprimé avec succès.";
                    break;
            }
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
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Marque</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <?php if ($product['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="img-thumbnail"
                                             style="max-width: 50px;">
                                    <?php else: ?>
                                        <i class="fas fa-image text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['brand_name'] ?? 'Non définie'); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Non définie'); ?></td>
                                <td><?php echo number_format($product['price'], 2); ?> MDH</td>
                                <td>
                                    <span class="badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $product['stock_quantity']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="product_form.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal<?php echo $product['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal de confirmation de suppression -->
                                    <div class="modal fade" id="deleteModal<?php echo $product['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmer la suppression</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Êtes-vous sûr de vouloir supprimer le produit "<?php echo htmlspecialchars($product['name']); ?>" ?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <button type="submit" name="delete_product" class="btn btn-danger">
                                                            Supprimer
                                                        </button>
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