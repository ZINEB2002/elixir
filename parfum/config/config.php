<?php
session_start();

// Configuration générale
define('SITE_NAME', 'Élixir');
define('SITE_URL', 'http://localhost/parfum');

// Chemins des dossiers
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Configuration des images
define('UPLOAD_PATH', ASSETS_PATH . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Configuration de sécurité
define('HASH_COST', 12); // Pour password_hash()

// Messages d'erreur
define('ERROR_MESSAGES', [
    'login_failed' => 'Email ou mot de passe incorrect',
    'email_exists' => 'Cet email est déjà utilisé',
    'username_exists' => 'Ce nom d\'utilisateur est déjà pris',
    'invalid_input' => 'Veuillez vérifier vos informations',
    'upload_failed' => 'Erreur lors du téléchargement du fichier',
    'unauthorized' => 'Accès non autorisé'
]);

// Fonction pour rediriger
function redirect($path) {
    // Si le chemin commence par '/', on le retire
    $path = ltrim($path, '/');
    
    // Si le chemin est vide, on redirige vers l'index
    if (empty($path)) {
        $path = 'index.php';
    }
    
    // Si le chemin ne se termine pas par .php et n'est pas une URL complète
    if (!str_ends_with($path, '.php') && !filter_var($path, FILTER_VALIDATE_URL)) {
        $path .= '.php';
    }
    
    header("Location: " . SITE_URL . '/' . $path);
    exit();
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Fonction pour sécuriser les entrées
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
} 