<?php
require_once '../config/config.php';
require_once '../config/database.php';

$user = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'role' => 'user'
];

// Si on modifie un utilisateur existant
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: users.php');
        exit;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = trim($_POST['password'] ?? '');
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } else {
        try {
            if (isset($_GET['id'])) {
                // Mise à jour
                if (!empty($password)) {
                    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, password = ? WHERE id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $role, password_hash($password, PASSWORD_DEFAULT), (int)$_GET['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $role, (int)$_GET['id']]);
                }
                header('Location: users.php?success=updated');
            } else {
                // Création
                if (empty($password)) {
                    $error = "Le mot de passe est requis pour la création d'un utilisateur.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$first_name, $last_name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
                    header('Location: users.php?success=created');
                }
            }
            if (!isset($error)) {
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Code d'erreur pour violation de contrainte unique
                $error = "Cette adresse email est déjà utilisée.";
            } else {
                $error = "Une erreur est survenue lors de l'enregistrement de l'utilisateur.";
            }
        }
    }
}

require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <?php echo isset($_GET['id']) ? 'Modifier l\'Utilisateur' : 'Nouvel Utilisateur'; ?>
        </h1>
        <a href="users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <?php if (isset($_GET['id'])): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id']; ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        Mot de passe <?php echo !isset($_GET['id']) ? '*' : ''; ?>
                    </label>
                    <input type="password" class="form-control" id="password" name="password" 
                           <?php echo !isset($_GET['id']) ? 'required' : ''; ?>>
                    <?php if (isset($_GET['id'])): ?>
                        <small class="form-text text-muted">
                            Laissez vide pour conserver le mot de passe actuel
                        </small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Rôle *</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo isset($_GET['id']) ? 'Mettre à jour' : 'Créer'; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 