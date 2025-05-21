<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    echo json_encode(['total' => 0]);
    exit;
}

try {
    // Calculer le total du panier
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(ci.quantity * p.price), 0) as total
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        JOIN products p ON ci.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();

    echo json_encode(['total' => (float)$result['total']]);
} catch (PDOException $e) {
    echo json_encode(['total' => 0]);
} 