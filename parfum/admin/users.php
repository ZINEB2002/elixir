<?php
require_once '../includes/admin_header.php';

// Traitement de la suppression
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = "L'utilisateur a été supprimé avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur.";
    }
    header('Location: users.php');
    exit;
}

// Récupération des utilisateurs
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT o.id) as total_orders,
           COALESCE(SUM(o.total_amount), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gestion des Clients</h1>
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

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Commandes</th>
                            <th>Total dépensé</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['total_orders']; ?></td>
                                <td><?php echo number_format($user['total_spent'], 2); ?> MDH</td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="user_form.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal<?php echo $user['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <!-- Modal de confirmation de suppression -->
                                        <div class="modal fade" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmer la suppression</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Êtes-vous sûr de vouloir supprimer l'utilisateur "<?php 
                                                            echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); 
                                                        ?>" ?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" name="delete_user" class="btn btn-danger">
                                                                Supprimer
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
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