<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

// Sanitizar e validar service_id
$service_id = sanitize($_GET['id'] ?? '');
if (empty($service_id) || !is_numeric($service_id)) {
    flash('ID de serviço inválido.', 'erro');
    header('Location: ../pages/my_services.php');
    exit();
}

$db = getConnection();
// Buscar serviço
try {
    $stmt = $db->prepare("SELECT * FROM services WHERE service_id = :id");
    $stmt->bindValue(':id', $service_id, PDO::PARAM_INT);
    $stmt->execute();
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        flash('Serviço não encontrado.', 'erro');
        header('Location: ../pages/my_services.php');
        exit();
    }

    if ($service['user_id'] != $_SESSION['user_id']) {
        flash('Não tens permissão para editar este serviço.', 'erro');
        header('Location: ../pages/my_services.php');
        exit();
    }

    // Buscar categorias
    $stmtCat = $db->prepare("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
    $stmtCat->execute();
    $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash('Erro ao carregar dados do serviço: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/my_services.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="edit-service-page">
    <h2 class="edit-service-title">Editar Serviço</h2>

    <form action="../actions/edit_service_action.php" method="post" class="edit-service-form">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="service_id" value="<?= escape($service['service_id']) ?>">

        <div class="form-group">
            <label for="title">Título:</label>
            <input type="text" id="title" name="title" value="<?= escape($service['title']) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Descrição:</label>
            <textarea id="description" name="description" rows="4" required><?= escape($service['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="price">Preço (em euros):</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= escape($service['price']) ?>" required>
        </div>

        <div class="form-group">
            <label for="delivery_time">Tempo de Entrega (dias):</label>
            <input type="number" id="delivery_time" name="delivery_time" min="1" value="<?= escape($service['delivery_time']) ?>" required>
        </div>

        <div class="form-group">
            <label for="category_id">Categoria:</label>
            <select id="category_id" name="category_id" required>
                <option value="">-- Selecione uma categoria --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= escape($cat['category_id']) ?>" <?= $cat['category_id'] == $service['category_id'] ? 'selected' : '' ?>>
                        <?= escape($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-primary">Salvar Alterações</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>