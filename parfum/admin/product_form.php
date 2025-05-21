<?php
require_once '../config/config.php';
require_once '../config/database.php';

$product = [
    'id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'stock_quantity' => '',
    'category_id' => '',
    'brand_id' => '',
    'image_url' => ''
];

// Si on modifie un produit existant
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: products.php');
        exit;
    }
}

// Récupération des catégories et marques pour les listes déroulantes
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands ORDER BY name")->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $image_url = $product['image_url']; // Garder l'ancienne image par défaut
    
    if (empty($name) || $price <= 0) {
        $error = "Le nom et le prix sont obligatoires et le prix doit être supérieur à 0.";
    } else {
        // Gestion de l'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $error = "Format de fichier non autorisé. Utilisez JPG, PNG ou GIF.";
            } else {
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Supprimer l'ancienne image si existe
                    if ($product['image_url'] && file_exists('../' . $product['image_url'])) {
                        unlink('../' . $product['image_url']);
                    }
                    $image_url = 'uploads/products/' . $new_filename;
                } else {
                    $error = "Erreur lors du téléchargement de l'image.";
                }
            }
        }
        
        if (!isset($error)) {
            try {
                if (isset($_GET['id'])) {
                    // Mise à jour
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET name = ?, description = ?, price = ?, stock_quantity = ?, 
                            category_id = ?, brand_id = ?, image_url = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name, $description, $price, $stock_quantity,
                        $category_id, $brand_id, $image_url, (int)$_GET['id']
                    ]);
                    header('Location: products.php?success=updated');
                } else {
                    // Création
                    $stmt = $pdo->prepare("
                        INSERT INTO products (name, description, price, stock_quantity, category_id, brand_id, image_url)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $name, $description, $price, $stock_quantity,
                        $category_id, $brand_id, $image_url
                    ]);
                    header('Location: products.php?success=created');
                }
                exit;
            } catch (PDOException $e) {
                $error = "Une erreur est survenue lors de l'enregistrement du produit.";
            }
        }
    }
}

require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <?php echo isset($_GET['id']) ? 'Modifier le Produit' : 'Nouveau Produit'; ?>
        </h1>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php if (isset($_GET['id'])): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id']; ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du produit *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo htmlspecialchars($product['description']); 
                            ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Prix *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="price" name="price" 
                                               value="<?php echo htmlspecialchars($product['price']); ?>" 
                                               step="0.01" min="0" required>
                                        <span class="input-group-text">MDH</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Quantité en stock *</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                           value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" 
                                           min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Catégorie</label>
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">Sélectionner une catégorie</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brand_id" class="form-label">Marque</label>
                                    <select class="form-select" id="brand_id" name="brand_id">
                                        <option value="">Sélectionner une marque</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?php echo $brand['id']; ?>" 
                                                    <?php echo $product['brand_id'] == $brand['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($brand['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="image" class="form-label">Image du produit</label>
                            <?php if ($product['image_url']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         alt="Image actuelle" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">
                                Formats acceptés : JPG, PNG, GIF. Taille maximale : 2MB
                            </small>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo isset($_GET['id']) ? 'Mettre à jour' : 'Créer'; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 