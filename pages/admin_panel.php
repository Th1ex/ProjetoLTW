<?php
// pages/admin_panel.php

// Inicia a sessão e verifica se o usuário está logado e é admin.
// Certifique-se de que o login define $_SESSION['is_admin'] (por exemplo, 1 para admin, 0 para não admin).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo "Acesso negado. Apenas administradores podem acessar essa página.";
    exit();
}

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

// Busca todas as categorias para listar
$stmt = $db->query("SELECT * FROM categories ORDER BY category_name ASC");
$categories = $stmt->fetchAll();
?>

<h2>Painel de Administração</h2>

<h3>Gerenciar Categorias</h3>
<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nome da Categoria</th>
      <th>Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($categories as $cat): ?>
    <tr>
      <td><?= htmlspecialchars($cat['category_id']) ?></td>
      <td><?= htmlspecialchars($cat['category_name']) ?></td>
      <td>
        <!-- Exemplo de links para editar ou excluir categoria -->
        <a href="edit_category.php?id=<?= htmlspecialchars($cat['category_id']) ?>">Editar</a> | 
        <a href="../actions/delete_category_action.php?id=<?= htmlspecialchars($cat['category_id']) ?>" onclick="return confirm('Tem certeza que deseja excluir essa categoria?');">Excluir</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>Adicionar Nova Categoria</h3>
<form action="../actions/add_category_action.php" method="post">
  <label for="category_name">Nome da Categoria:</label>
  <input type="text" id="category_name" name="category_name" required>
  <button type="submit">Adicionar</button>
</form>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
