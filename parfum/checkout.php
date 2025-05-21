<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('login.php');
}

// Initialiser les variables
$error = '';
$cart_items = [];
$total = 0;

// Récupérer les articles du panier
try {
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.price, p.stock_quantity, p.image_url
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        JOIN carts c ON ci.cart_id = c.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();

    // Calculer le total
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du panier : " . $e->getMessage());
    $error = "Une erreur est survenue lors de la récupération de votre panier.";
}

// Traitement du formulaire de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $notes = trim($_POST['notes'] ?? '');
    $payment_method = $_POST['payment_method'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || 
        empty($address) || empty($city) || empty($postal_code)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            // Démarrer une transaction
            $pdo->beginTransaction();

            // Créer la commande
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, total_amount, status, shipping_address, shipping_city,
                    shipping_postal_code, shipping_phone, shipping_first_name, shipping_last_name,
                    payment_method, created_at
                ) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                $total,
                $address,
                $city,
                $postal_code,
                $phone,
                $first_name,
                $last_name,
                $payment_method
            ]);
            $order_id = $pdo->lastInsertId();

            // Ajouter les articles de la commande
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            foreach ($cart_items as $item) {
                // Vérifier le stock disponible
                if ($item['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Stock insuffisant pour le produit : " . $item['name']);
                }

                $stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);

                // Mettre à jour le stock
                $new_stock = $item['stock_quantity'] - $item['quantity'];
                $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?")
                    ->execute([$new_stock, $item['product_id']]);
            }

            // Vider le panier
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = (SELECT id FROM carts WHERE user_id = ?)")
                ->execute([$_SESSION['user_id']]);
            $pdo->prepare("DELETE FROM carts WHERE user_id = ?")
                ->execute([$_SESSION['user_id']]);

            // Valider la transaction
            $pdo->commit();

            // Stocker l'ID de la commande en session
            $_SESSION['last_order_id'] = $order_id;

            // Rediriger selon le mode de paiement
            if ($payment_method === 'credit_card' || $payment_method === 'paypal') {
                // Rediriger vers la page de paiement
                header("Location: " . SITE_URL . "/payment.php");
            } else {
                // Pour le paiement à la livraison, rediriger directement vers la confirmation
                header("Location: " . SITE_URL . "/confirmation.php?id=" . $order_id);
            }
            exit();
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $pdo->rollBack();
            error_log("Erreur lors de la commande : " . $e->getMessage());
            $error = 'Une erreur est survenue lors du traitement de votre commande : ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">Finaliser la commande</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Formulaire de livraison -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Informations de livraison</h5>
                    <form method="POST" action="checkout.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse *</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Ville *</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Code postal *</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes de livraison</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>

                        <!-- Section Mode de Paiement -->
                        <div class="mb-4">
                            <h5 class="mb-3">Mode de Paiement</h5>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                <label class="form-check-label" for="credit_card">
                                    <i class="fas fa-credit-card me-2"></i>Carte Bancaire
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                <label class="form-check-label" for="paypal">
                                    <i class="fab fa-paypal me-2"></i>PayPal
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cash_on_delivery" value="cash_on_delivery">
                                <label class="form-check-label" for="cash_on_delivery">
                                    <i class="fas fa-money-bill-wave me-2"></i>Paiement à la livraison
                                </label>
                            </div>
                        </div>

                        <!-- Section Confirmation -->
                        <div class="mb-4">
                            <h5 class="mb-3">Confirmation de la Commande</h5>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-lock me-2"></i>Confirmer et Payer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Résumé de la commande -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Résumé de la commande</h5>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>
                                <?php echo htmlspecialchars($item['name']); ?> 
                                <small class="text-muted">x<?php echo $item['quantity']; ?></small>
                            </span>
                            <span><?php echo number_format($item['price'] * $item['quantity'], 2); ?> MDH</span>
                        </div>
                    <?php endforeach; ?>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total</span>
                        <span><?php echo number_format($total, 2); ?> MDH</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Livraison</span>
                        <span>Gratuite</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total</strong>
                        <strong><?php echo number_format($total, 2); ?> MDH</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 