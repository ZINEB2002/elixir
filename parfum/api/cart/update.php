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
if (!$data || !isset($data['product_id']) || !isset($data['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);
$quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);

if (!$product_id || !$quantity || $quantity < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    // Vérifier si le produit existe et est en stock
    $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
        exit;
    }

    if ($quantity > $product['stock_quantity']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
        exit;
    }

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

    // Mettre à jour la quantité
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $cart_item['id']]);

    // Calculer le nouveau total pour ce produit
    $stmt = $pdo->prepare("
        SELECT p.price * ci.quantity as total
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.id = ?
    ");
    $stmt->execute([$cart_item['id']]);
    $item_total = $stmt->fetch();

    // Calculer le total général du panier
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(ci.quantity * p.price), 0) as total
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        JOIN products p ON ci.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_total = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'item_total' => (float)$item_total['total'],
        'cart_total' => (float)$cart_total['total']
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du panier']);
}