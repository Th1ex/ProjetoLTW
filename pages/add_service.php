<?php
// pages/add_service.php

// Verifica se o user está logado
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

// Buscar todas as categorias existentes para preencher o <select>
$stmt = $db->prepare("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<h2>Adicionar Novo Serviço</h2>

<form action="../actions/add_service_action.php" method="post">
  <label for="title">Título do Serviço:</label>
  <input type="text" id="title" name="title" required>

  <label for="description">Descrição:</label>
  <textarea id="description" name="description" rows="4" required></textarea>

  <label for="price">Preço (em euros):</label>
  <input type="number" step="0.01" id="price" name="price" required>

  <label for="delivery_time">Tempo de Entrega (dias):</label>
  <input type="number" id="delivery_time" name="delivery_time" min="1" required>

  <label for="category_id">Categoria:</label>
  <select id="category_id" name="category_id" required>
    <option value="">-- Selecione uma categoria --</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= htmlspecialchars($cat['category_id']) ?>">
        <?= htmlspecialchars($cat['category_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Adicionar Serviço</button>
</form>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
