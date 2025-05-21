<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    // Rediriger directement vers la page de connexion
    $redirect_url = SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER']);
    header('Location: ' . $redirect_url);
    exit;
}

// Si l'utilisateur est connecté, continuer avec la réponse JSON
header('Content-Type: application/json');
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
    // Vérifier si le produit existe et est en stock
    $stmt = $pdo->prepare("SELECT id, stock_quantity FROM products WHERE id = ? AND stock_quantity > 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Produit non trouvé ou hors stock']);
        exit;
    }

    // Ajouter après la récupération du produit
    if ($product['stock_quantity'] <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Produit en rupture de stock']);
        exit;
    }

    // Récupérer ou créer le panier de l'utilisateur
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart = $stmt->fetch();

    if (!$cart) {
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_id = $pdo->lastInsertId();
    } else {
        $cart_id = $cart['id'];
    }

    // Vérifier si le produit est déjà dans le panier
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $cart_item = $stmt->fetch();

    if ($cart_item) {
        // Mettre à jour la quantité si le produit est déjà dans le panier
        $new_quantity = $cart_item['quantity'] + 1;
        if ($new_quantity > $product['stock_quantity']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $cart_item['id']]);
    } else {
        // Ajouter le produit au panier
        $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$cart_id, $product_id]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout au panier']);
} 