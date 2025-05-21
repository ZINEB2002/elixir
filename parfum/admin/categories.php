<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Traitement de la suppression
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: categories.php?success=deleted');
    exit;
}

require_once '../includes/admin_header.php';

// Récupération des catégories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gestion des Catégories</h1>
        <a href="category_form.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Catégorie
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php
            switch ($_GET['success']) {
                case 'created':
                    echo "La catégorie a été créée avec succès.";
                    break;
                case 'updated':
                    echo "La catégorie a été mise à jour avec succès.";
                    break;
                case 'deleted':
                    echo "La catégorie a été supprimée avec succès.";
                    break;
            }
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
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <a href="category_form.php?id=<?php echo $category['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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