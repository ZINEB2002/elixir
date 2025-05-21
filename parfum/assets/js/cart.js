document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons d'ajout au panier
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            addToCart(productId);
        });
    });
    
    function addToCart(productId) {
        fetch('/api/cart/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Produit ajouté avec succès
                // Mettre à jour l'interface utilisateur (compteur de panier, etc.)
                updateCartUI();
            } else {
                // Si l'utilisateur n'est pas connecté (code 401)
                if (response.status === 401) {
                    // Rediriger vers la page de connexion
                    window.location.href = SITE_URL + '/login.php';
                } else {
                    // Afficher d'autres erreurs si nécessaire
                    console.error(data.message);
                }
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }
    
    // Fonction pour mettre à jour l'interface utilisateur du panier
    function updateCartUI() {
        // Code pour mettre à jour le compteur du panier, etc.
    }});