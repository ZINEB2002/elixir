<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/admin_header.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    redirect('/login.php');
}

// Récupérer les statistiques
try {
    // Nombre total d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();

    // Nombre total de produits
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = $stmt->fetchColumn();

    // Nombre total de commandes
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = $stmt->fetchColumn();

    // Chiffre d'affaires total
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'");
    $totalRevenue = $stmt->fetchColumn() ?: 0;

    // Dernières commandes
    $stmt = $pdo->query("
        SELECT o.*, u.email as user_email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recentOrders = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur d'administration : " . $e->getMessage());
    $error = "Une erreur est survenue lors de la récupération des données";
}

// Récupérer les statistiques
$stats = [
    'products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'revenue' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn()
];

// Récupérer les dernières commandes
$latest_orders = $pdo->query("
    SELECT o.*, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Tableau de bord</h1>

    <!-- Statistiques -->
    <div class="row">
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Produits</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['products']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Commandes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['orders']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Utilisateurs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['users']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dernières commandes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Dernières commandes</h6>
            <a href="orders.php" class="btn btn-sm btn-primary">Voir toutes</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latest_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td><?php echo number_format($order['total_amount'], 2); ?> MDH</td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeClass($order['status']); ?>">
                                        <?php echo getStatusLabel($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Fonction pour obtenir la classe CSS du badge selon le statut
function getStatusBadgeClass($status) {
    return match($status) {
        'pending' => 'warning',
        'processing' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}

// Fonction pour obtenir le libellé du statut
function getStatusLabel($status) {
    return match($status) {
        'pending' => 'En attente',
        'processing' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
        default => 'Inconnu'
    };
}

require_once '../includes/admin_footer.php';
?> 