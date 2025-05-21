document.addEventListener('DOMContentLoaded', function() {
    // Gestion du panier
    updateCartCount();
    initializeCartListeners();
    
    // Gestion des filtres
    initializeFilters();
    
    // Gestion des animations
    initializeAnimations();
});

// Mise à jour du compteur du panier
function updateCartCount() {
    fetch('/parfum/api/cart/count.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.count;
            }
        })
        .catch(error => console.error('Erreur:', error));
}

// Initialisation des écouteurs d'événements pour le panier
function initializeCartListeners() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });
}

// Ajout au panier
function addToCart(productId) {
    fetch('/parfum/api/cart/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Produit ajouté au panier', 'success');
            updateCartCount();
        } else {
            // Vérifier si l'utilisateur doit être redirigé vers la page de connexion
            if (data.redirect_to_login && data.redirect_url) {
                // Redirection immédiate vers la page de connexion sans message
                window.location.href = data.redirect_url;
            } else {
                showNotification(data.message || 'Erreur lors de l\'ajout au panier', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'ajout au panier', 'error');
    });
}

// Initialisation des filtres
function initializeFilters() {
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            
            fetch(`/parfum/catalogue.php?${params.toString()}`)
                .then(response => response.text())
                .then(html => {
                    const productGrid = document.querySelector('.product-grid');
                    if (productGrid) {
                        productGrid.innerHTML = html;
                        initializeCartListeners();
                    }
                })
                .catch(error => console.error('Erreur:', error));
        });
    }
}

// Initialisation des animations
function initializeAnimations() {
    // Animation au défilement
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    });

    document.querySelectorAll('.animate-on-scroll').forEach((element) => {
        observer.observe(element);
    });
}

// Affichage des notifications
function showNotification(message, type = 'info') {
    const notifications = document.getElementById('notifications');
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    notifications.appendChild(notification);

    // Supprimer la notification après 3 secondes
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Gestion du formulaire de recherche
const searchForm = document.querySelector('.search-form');
if (searchForm) {
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const searchInput = this.querySelector('input[name="q"]');
        const searchTerm = searchInput.value.trim();
        
        if (searchTerm.length > 0) {
            window.location.href = `/parfum/recherche.php?q=${encodeURIComponent(searchTerm)}`;
        }
    });
}

// Gestion du slider de prix
const priceRange = document.querySelector('.price-range');
if (priceRange) {
    const minPrice = document.getElementById('min-price');
    const maxPrice = document.getElementById('max-price');
    
    priceRange.addEventListener('input', function() {
        const value = this.value;
        minPrice.textContent = value;
        maxPrice.textContent = this.max;
    });
}

// Gestion de la quantité dans le panier
function updateQuantity(productId, newQuantity) {
    fetch('/parfum/api/cart/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartTotal();
            showNotification('Quantité mise à jour', 'success');
        } else {
            showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la mise à jour', 'error');
    });
}

// Mise à jour du total du panier
function updateCartTotal() {
    fetch('/parfum/api/cart/total.php')
        .then(response => response.json())
        .then(data => {
            const totalElement = document.querySelector('.cart-total');
            if (totalElement) {
                totalElement.textContent = `${data.total.toFixed(2)} €`;
            }
        })
        .catch(error => console.error('Erreur:', error));
} 