<?php
require_once 'includes/header.php';

// Vérifier si l'utilisateur est déjà connecté
if (isLoggedIn()) {
    redirect('/');
}

// Fonction de nettoyage des chaînes
function cleanString($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

$error = '';
$success = '';

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanString($_POST['username'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = cleanString($_POST['first_name'] ?? '');
    $last_name = cleanString($_POST['last_name'] ?? '');

    // Validation des données
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères';
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé';
        } else {
            // Vérifier si le nom d'utilisateur existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Ce nom d\'utilisateur est déjà pris';
            } else {
                // Créer le nouvel utilisateur
                $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, role)
                    VALUES (?, ?, ?, ?, ?, 'client')
                ");
                
                try {
                    $stmt->execute([$username, $email, $hashed_password, $first_name, $last_name]);
                    $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                } catch (PDOException $e) {
                    $error = 'Une erreur est survenue lors de l\'inscription';
                }
            }
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Inscription</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <br>
                            <a href="login.php">Cliquez ici pour vous connecter</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="register.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Nom d'utilisateur *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                           required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">Prénom</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Nom</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Mot de passe *</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           required>
                                    <small class="form-text text-muted">
                                        Le mot de passe doit contenir au moins 8 caractères
                                    </small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           required>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="terms" 
                                       name="terms" 
                                       required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="terms.php">conditions d'utilisation</a> et la 
                                    <a href="privacy.php">politique de confidentialité</a>
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">S'inscrire</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 