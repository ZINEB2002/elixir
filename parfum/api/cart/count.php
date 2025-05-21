<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    // Compter le nombre total d'articles dans le panier
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(ci.quantity), 0) as count
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();

    echo json_encode(['count' => (int)$result['count']]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
} 