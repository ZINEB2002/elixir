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
if (!$data || !isset($data['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$order_id = filter_var($data['order_id'], FILTER_VALIDATE_INT);
if (!$order_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de commande invalide']);
    exit;
}

try {
    // Vérifier si la commande existe et appartient à l'utilisateur
    $stmt = $pdo->prepare("
        SELECT o.*, oi.product_id, oi.quantity
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id = ? AND o.user_id = ? AND o.status = 'pending'
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order_items = $stmt->fetchAll();

    if (empty($order_items)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Commande non trouvée ou non annulable']);
        exit;
    }

    // Démarrer une transaction
    $pdo->beginTransaction();

    // Restaurer le stock
    foreach ($order_items as $item) {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity + ? 
            WHERE id = ?
        ");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Mettre à jour le statut de la commande
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);

    // Valider la transaction
    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'annulation de la commande']);
} 