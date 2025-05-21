<?php
require_once 'config/config.php';
require_once 'includes/header.php';

try {
    // Fonction de nettoyage des chaînes
    function cleanString($str) {
        return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
    }

    // Récupérer les paramètres de filtrage avec validation
    $search = isset($_GET['search']) ? cleanString($_GET['search']) : '';
    $category_id = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
    $brand_id = filter_input(INPUT_GET, 'brand', FILTER_VALIDATE_INT);
    $min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
    $max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
    $sort = isset($_GET['sort']) ? cleanString($_GET['sort']) : '';
    $page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1);
    $per_page = 12;

    // Construire la requête SQL de base
    $sql = "
        SELECT p.*, b.name as brand_name, c.name as category_name
        FROM products p
        JOIN brands b ON p.brand_id = b.id
        JOIN categories c ON p.category_id = c.id
        WHERE p.stock_quantity > 0
    ";
    $params = [];

    // Ajouter les conditions de filtrage
    if ($search) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR b.name LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }

    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }

    if ($brand_id) {
        $sql .= " AND p.brand_id = ?";
        $params[] = $brand_id;
    }

    if ($min_price !== null && $min_price !== false) {
        $sql .= " AND p.price >= ?";
        $params[] = $min_price;
    }

    if ($max_price !== null && $max_price !== false) {
        $sql .= " AND p.price <= ?";
        $params[] = $max_price;
    }

    // Récupérer le nombre total de produits
    $count_sql = str_replace("p.*, b.name as brand_name, c.name as category_name", "COUNT(*) as total", $sql);
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetch()['total'];
    $total_pages = ceil($total_products / $per_page);

    // Ajouter le tri
    switch ($sort) {
        case 'price_asc':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY p.name ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY p.name DESC";
            break;
        default:
            $sql .= " ORDER BY p.created_at DESC";
    }

    // Ajouter la pagination
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = ($page - 1) * $per_page;

    // Exécuter la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Récupérer les catégories et marques pour les filtres
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    $brands = $pdo->query("SELECT * FROM brands ORDER BY name")->fetchAll();

    // Récupérer les prix min et max avec COALESCE pour éviter les valeurs nulles
    $price_range = $pdo->query("
        SELECT 
            COALESCE(MIN(price), 0) as min_price,
            COALESCE(MAX(price), 0) as max_price 
        FROM products 
        WHERE stock_quantity > 0
    ")->fetch();

} catch (PDOException $e) {
    // Log l'erreur et afficher un message générique
    error_log("Erreur de base de données : " . $e->getMessage());
    $error = "Une erreur est survenue lors de la récupération des produits.";
}
?>

<div class="container py-5">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Filtres -->
            <div class="col-md-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Filtres</h5>
                        <form id="filter-form" method="GET">
                            <!-- Recherche -->
                            <div class="mb-3">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>

                            <!-- Catégories -->
                            <div class="mb-3">
                                <label for="category" class="form-label">Catégories</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">Toutes les catégories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"
                                                <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Marques -->
                            <div class="mb-3">
                                <label for="brand" class="form-label">Marques</label>
                                <select class="form-select" id="brand" name="brand">
                                    <option value="">Toutes les marques</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>"
                                                <?php echo $brand_id == $brand['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Prix -->
                            <div class="mb-3">
                                <label class="form-label">Prix</label>
                                <div class="row g-2">
                                    <div class="col">
                                        <input type="number" class="form-control" name="min_price" 
                                               placeholder="Min" min="0" step="0.01"
                                               value="<?php echo $min_price; ?>">
                                    </div>
                                    <div class="col">
                                        <input type="number" class="form-control" name="max_price" 
                                               placeholder="Max" min="0" step="0.01"
                                               value="<?php echo $max_price; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Tri -->
                            <div class="mb-3">
                                <label for="sort" class="form-label">Trier par</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="">Plus récents</option>
                                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>
                                        Prix croissant
                                    </option>
                                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>
                                        Prix décroissant
                                    </option>
                                    <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>
                                        Nom A-Z
                                    </option>
                                    <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>
                                        Nom Z-A
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Appliquer les filtres</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Liste des produits -->
            <div class="col-md-9">
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        Aucun produit ne correspond à vos critères de recherche.
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="card-text text-muted">
                                            <?php echo htmlspecialchars($product['brand_name']); ?>
                                        </p>
                                        <p class="card-text">
                                            <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0"><?php echo number_format($product['price'], 2); ?> MDH</span>
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                            <button class="btn btn-primary add-to-cart" 
                                                    data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-shopping-cart"></i> Ajouter
                                            </button>
                                            <?php else: ?>
                                            <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary">
                                                <i class="fas fa-shopping-cart"></i> Ajouter
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Gestion du formulaire de filtrage
document.getElementById('filter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Récupérer toutes les valeurs du formulaire
    const formData = new FormData(this);
    const params = new URLSearchParams();
    
    // Ajouter chaque paramètre non vide à l'URL
    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Rediriger vers la même page avec les paramètres
    window.location.href = 'catalogue.php?' + params.toString();
});
</script>

<?php require_once 'includes/footer.php'; ?> 