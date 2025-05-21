<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('login.php');
}

// Vérifier si une commande est en cours
if (!isset($_SESSION['last_order_id'])) {
    redirect('cart.php');
}

// Récupérer les informations de la commande
$stmt = $pdo->prepare("
    SELECT o.*, u.email, u.first_name, u.last_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ? AND o.status = 'pending'
");
$stmt->execute([$_SESSION['last_order_id'], $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('cart.php');
}

// Traitement du paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer les données du formulaire
        $card_number = $_POST['card_number'] ?? '';
        $expiry_date = $_POST['expiry_date'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        $card_name = $_POST['card_name'] ?? '';

        // Validation basique des données
        if (empty($card_number) || empty($expiry_date) || empty($cvv) || empty($card_name)) {
            throw new Exception("Tous les champs sont obligatoires.");
        }

        // Simuler un traitement de paiement réussi
        // Dans un environnement réel, vous devriez utiliser une API de paiement comme Stripe
        
        // Mettre à jour le statut de la commande
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = 'processing',
                payment_status = 'paid',
                payment_method = ?,
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$order['payment_method'], $order['id'], $_SESSION['user_id']]);

        // Vider le panier
        unset($_SESSION['cart']);
        unset($_SESSION['cart_total']);

        // Rediriger vers la page de confirmation
        $_SESSION['success'] = "Votre paiement a été traité avec succès.";
        redirect('confirmation.php?id=' . $order['id']);
    } catch (Exception $e) {
        $_SESSION['error'] = "Une erreur est survenue lors du traitement du paiement : " . $e->getMessage();
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Paiement sécurisé</h2>
                    
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-lock me-2"></i>
                        Votre paiement est sécurisé. Toutes les informations sont cryptées.
                    </div>

                    <div class="mb-4">
                        <h5>Récapitulatif de la commande</h5>
                        <p class="mb-1">Commande #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        <p class="mb-1">Total à payer : <strong><?php echo number_format($order['total_amount'], 2); ?> MDH</strong></p>
                    </div>

                    <form method="POST" id="payment-form" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="card-number" class="form-label">Numéro de carte</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                <input type="text" class="form-control" id="card-number" name="card_number"
                                       placeholder="1234 5678 9012 3456" required
                                       pattern="[0-9\s]{13,19}" maxlength="19">
                            </div>
                            <div class="invalid-feedback">
                                Veuillez entrer un numéro de carte valide.
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="expiry-date" class="form-label">Date d'expiration</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="text" class="form-control" id="expiry-date" name="expiry_date"
                                           placeholder="MM/AA" required
                                           pattern="(0[1-9]|1[0-2])\/([0-9]{2})" maxlength="5">
                                </div>
                                <div class="invalid-feedback">
                                    Format invalide (MM/AA).
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="cvv" class="form-label">Code de sécurité (CVV)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="text" class="form-control" id="cvv" name="cvv"
                                           placeholder="123" required
                                           pattern="[0-9]{3,4}" maxlength="4">
                                </div>
                                <div class="invalid-feedback">
                                    Code de sécurité invalide.
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="card-name" class="form-label">Nom sur la carte</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="card-name" name="card_name"
                                       placeholder="JEAN DUPONT" required>
                            </div>
                            <div class="invalid-feedback">
                                Veuillez entrer le nom figurant sur la carte.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>
                                Payer <?php echo number_format($order['total_amount'], 2); ?> MDH
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Formatage automatique du numéro de carte
document.getElementById('card-number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    let formattedValue = '';
    for(let i = 0; i < value.length; i++) {
        if(i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    e.target.value = formattedValue;
});

// Formatage automatique de la date d'expiration
document.getElementById('expiry-date').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0,2) + '/' + value.substring(2);
    }
    e.target.value = value;
});

// Validation du formulaire
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require_once 'includes/footer.php'; ?> 