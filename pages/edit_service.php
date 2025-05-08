<?php
// pages/edit_service.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$service_id = $_GET['id'] ?? null;
if (!$service_id || !is_numeric($service_id)) {
    die("ID de serviço inválido.");
}

$db = getConnection();

// Buscar o serviço e garantir que pertence ao user logado
$stmt = $db->prepare("
    SELECT * FROM services
    WHERE service_id = :id
");
$stmt->bindValue(':id', $service_id, PDO::PARAM_INT);
$stmt->execute();
$service = $stmt->fetch();

if (!$service) {
    die("Serviço não encontrado.");
}

if ($service['user_id'] != $_SESSION['user_id']) {
    die("Não tens permissão para editar este serviço.");
}

// Buscamos também as categorias para o <select>
$stmtCat = $db->prepare("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$stmtCat->execute();
$categories = $stmtCat->fetchAll();
?>

<div class="edit-service-page">
    <h2 class="edit-service-title">Editar Serviço</h2>

    <form action="../actions/edit_service_action.php" method="post">
        <!-- ID escondido -->
        <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['service_id']) ?>">

        <label for="title">Título:</label>
        <input type="text" id="title" name="title" 
               value="<?= htmlspecialchars($service['title']) ?>" required>

        <label for="description">Descrição:</label>
        <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($service['description']) ?></textarea>

        <label for="price">Preço (em euros):</label>
        <input type="number" step="0.01" id="price" name="price" 
               value="<?= htmlspecialchars($service['price']) ?>" required>

        <label for="delivery_time">Tempo de Entrega (dias):</label>
        <input type="number" id="delivery_time" name="delivery_time" min="1"
               value="<?= htmlspecialchars($service['delivery_time']) ?>" required>

        <label for="category_id">Categoria:</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Selecione uma categoria --</option>
            <?php foreach ($categories as $cat): 
                $selected = ($cat['category_id'] == $service['category_id']) ? 'selected' : '';
            ?>
                <option value="<?= htmlspecialchars($cat['category_id']) ?>" <?= $selected ?>>
                    <?= htmlspecialchars($cat['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Salvar Alterações</button>
    </form>
</div>
<?php
require_once __DIR__ . '/../templates/footer.php';
?>
