<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Traitement de la suppression
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    try {
        // Récupérer le logo avant la suppression
        $stmt = $pdo->prepare("SELECT logo_url FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        $brand = $stmt->fetch();
        
        // Supprimer la marque
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        
        // Supprimer le logo si existe
        if ($brand && $brand['logo_url'] && file_exists('../' . $brand['logo_url'])) {
            unlink('../' . $brand['logo_url']);
        }
        
        header('Location: brands.php?success=deleted');
        exit;
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression de la marque.";
    }
}

require_once '../includes/admin_header.php';

// Récupération des marques
$stmt = $pdo->query("SELECT * FROM brands ORDER BY name");
$brands = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gestion des Marques</h1>
        <a href="brand_form.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Marque
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php
            switch ($_GET['success']) {
                case 'created':
                    echo "La marque a été créée avec succès.";
                    break;
                case 'updated':
                    echo "La marque a été mise à jour avec succès.";
                    break;
                case 'deleted':
                    echo "La marque a été supprimée avec succès.";
                    break;
            }
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brands as $brand): ?>
                            <tr>
                                <td><?php echo $brand['id']; ?></td>
                                <td>
                                    <?php if ($brand['logo_url']): ?>
                                        <img src="<?php echo htmlspecialchars($brand['logo_url']); ?>" 
                                             alt="Logo" style="max-height: 50px;">
                                    <?php else: ?>
                                        <i class="fas fa-image text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($brand['name']); ?></td>
                                <td><?php echo htmlspecialchars($brand['description']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($brand['created_at'])); ?></td>
                                <td>
                                    <a href="brand_form.php?id=<?php echo $brand['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette marque ?');">
                                        <input type="hidden" name="id" value="<?php echo $brand['id']; ?>">
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