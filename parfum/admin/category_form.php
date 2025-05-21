<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    if (empty($name)) {
        $error = "Le nom de la catégorie est requis.";
    } else {
        try {
            if ($id) {
                // Mise à jour
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $description, $id]);
                header('Location: categories.php?success=updated');
            } else {
                // Création
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                header('Location: categories.php?success=created');
            }
            exit;
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de l'enregistrement de la catégorie.";
        }
    }
}

// Récupération des données si édition
$category = ['name' => '', 'description' => ''];
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $category = $stmt->fetch();
    if (!$category) {
        header('Location: categories.php');
        exit;
    }
}

require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?php echo isset($_GET['id']) ? 'Modifier' : 'Nouvelle'; ?> Catégorie</h1>
        <a href="categories.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <?php if (isset($_GET['id'])): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo htmlspecialchars($category['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php 
                        echo htmlspecialchars($category['description']); 
                    ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?> 