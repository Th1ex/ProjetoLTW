<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';


require_login();

$db = getConnection();

// Busca categorias para o select
try {
    $stmt = $db->prepare("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash('Erro ao carregar categorias: ' . $e->getMessage(), 'erro');
    $categories = [];
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="add-service-page">
  <h2>Adicionar Novo Serviço</h2>

  <form action="../actions/add_service_action.php" method="post" enctype="multipart/form-data">
    <!-- Token de segurança (fora da grid de campos) -->
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="form-group">
      <label for="title">Título do Serviço:</label>
      <input type="text" id="title" name="title" required>
    </div>

    <div class="form-group">
      <label for="description">Descrição:</label>
      <textarea id="description" name="description" rows="4" required></textarea>
    </div>

    <div class="form-group">
      <label for="price">Preço (em euros):</label>
      <input type="number" step="0.01" id="price" name="price" required>
    </div>

    <div class="form-group">
      <label for="delivery_time">Tempo de Entrega (dias):</label>
      <input type="number" id="delivery_time" name="delivery_time" min="1" required>
    </div>

    <div class="form-group">
      <label for="category_id">Categoria:</label>
      <select id="category_id" name="category_id" required>
        <option value="">-- Selecione uma categoria --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= escape($cat['category_id']) ?>"><?= escape($cat['category_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="files">Ficheiros (imagens e/ou vídeos):</label>
      <input type="file" id="files" name="files[]" multiple accept="image/*,video/*">
    </div>

    <button type="submit">Adicionar Serviço</button>
  </form>
</div>


<?php
require_once __DIR__ . '/../templates/footer.php';
?>