<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
    exit;
}

// Vérifier si les données sont envoyées en JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);
if (!$product_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de produit invalide']);
    exit;
}

try {
    // Récupérer le panier de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT ci.id 
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        WHERE c.user_id = ? AND ci.product_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $cart_item = $stmt->fetch();

    if (!$cart_item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Produit non trouvé dans le panier']);
        exit;
    }

    // Supprimer le produit du panier
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt->execute([$cart_item['id']]);

    // Vérifier si le panier est vide
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetchColumn();

    // Si le panier est vide, le supprimer
    if ($cart_count === 0) {
        $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du produit']);
} 