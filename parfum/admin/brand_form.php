<?php
require_once '../config/config.php';
require_once '../config/database.php';

$brand = [
    'id' => '',
    'name' => '',
    'description' => '',
    'logo_url' => ''
];

// Si on modifie une marque existante
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $brand = $stmt->fetch();
    
    if (!$brand) {
        header('Location: brands.php');
        exit;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $logo_url = $brand['logo_url']; // Garder l'ancien logo par défaut
    
    if (empty($name)) {
        $error = "Le nom de la marque est requis.";
    } else {
        // Gestion du logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/brands/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $error = "Format de fichier non autorisé. Utilisez JPG, PNG ou GIF.";
            } else {
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    // Supprimer l'ancien logo si existe
                    if ($brand['logo_url'] && file_exists('../' . $brand['logo_url'])) {
                        unlink('../' . $brand['logo_url']);
                    }
                    $logo_url = 'uploads/brands/' . $new_filename;
                } else {
                    $error = "Erreur lors du téléchargement du logo.";
                }
            }
        }
        
        if (!isset($error)) {
            try {
                if (isset($_GET['id'])) {
                    // Mise à jour
                    $stmt = $pdo->prepare("UPDATE brands SET name = ?, description = ?, logo_url = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $logo_url, (int)$_GET['id']]);
                    header('Location: brands.php?success=updated');
                } else {
                    // Création
                    $stmt = $pdo->prepare("INSERT INTO brands (name, description, logo_url) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $description, $logo_url]);
                    header('Location: brands.php?success=created');
                }
                exit;
            } catch (PDOException $e) {
                $error = "Une erreur est survenue lors de l'enregistrement de la marque.";
            }
        }
    }
}

require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <?php echo isset($_GET['id']) ? 'Modifier la Marque' : 'Nouvelle Marque'; ?>
        </h1>
        <a href="brands.php" class="btn btn-secondary">
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

                <div class="mb-3">
                    <label for="name" class="form-label">Nom de la marque *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo htmlspecialchars($brand['name']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php 
                        echo htmlspecialchars($brand['description']); 
                    ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <?php if ($brand['logo_url']): ?>
                        <div class="mb-2">
                            <img src="<?php echo htmlspecialchars($brand['logo_url']); ?>" 
                                 alt="Logo actuel" style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    <small class="form-text text-muted">
                        Formats acceptés : JPG, PNG, GIF. Taille maximale : 2MB
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo isset($_GET['id']) ? 'Mettre à jour' : 'Créer'; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 