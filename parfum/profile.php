<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/header.php';

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Récupérer l'historique des commandes
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as total_items 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    
    // Validation basique
    $errors = [];
    if (empty($firstname)) $errors[] = "Le prénom est requis.";
    if (empty($lastname)) $errors[] = "Le nom est requis.";
    if (empty($email)) $errors[] = "L'email est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email n'est pas valide.";
    
    // Vérifier si l'email existe déjà pour un autre utilisateur
    if ($email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Cet email est déjà utilisé par un autre compte.";
        }
    }
    
    if (empty($errors)) {
        // Mise à jour du profil
        $stmt = $pdo->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, email = ?, phone = ?, 
                address = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $firstname, $lastname, $email, $phone,
            $address,
            $_SESSION['user_id']
        ]);
        
        $success = "Votre profil a été mis à jour avec succès.";
        
        // Mettre à jour les données de session
        $_SESSION['user_name'] = $firstname . ' ' . $lastname;
        $_SESSION['user_email'] = $email;
        
        // Recharger les données utilisateur
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Menu latéral -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Mon Compte</h5>
                    <div class="list-group list-group-flush">
                        <a href="#profile" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i> Mon Profil
                        </a>
                        <a href="#orders" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag me-2"></i> Mes Commandes
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-9">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Section Profil -->
            <div id="profile" class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">Mon Profil</h4>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname" class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" 
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastname" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" 
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                       value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="country" class="form-label">Pays</label>
                                <input type="text" class="form-control" id="country" name="country" 
                                       value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>

            <!-- Section Commandes -->
            <div id="orders" class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Mes Commandes</h4>
                    <?php if (empty($orders)): ?>
                        <p class="text-muted">Vous n'avez pas encore passé de commande.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>N° Commande</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                        <th>Articles</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo number_format($order['total_amount'], 2); ?> MDH</td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $order['status'] === 'completed' ? 'success' : 
                                                        ($order['status'] === 'pending' ? 'warning' : 'secondary'); 
                                                ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $order['total_items']; ?> article(s)</td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Détails
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>