<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';


$db = getConnection();

// Sanitizar filtros de busca
$searchQuery    = sanitize($_GET['q'] ?? '');
$filterCategory = sanitize($_GET['category_id'] ?? '');
$minPrice       = sanitize($_GET['min_price'] ?? '');
$maxPrice       = sanitize($_GET['max_price'] ?? '');
$minRating      = sanitize($_GET['min_rating'] ?? '');

// Construir query
$sql = "
 SELECT s.service_id, s.title, s.price, s.image,
        s.user_id AS owner_id,
        c.category_name,
        u.username AS freelancer_name,
        s.created_at,
        COALESCE(AVG(r.rating),0) AS avg_rating
 FROM services s
 JOIN categories c ON s.category_id = c.category_id
 JOIN users      u ON s.user_id     = u.user_id
 LEFT JOIN orders  o ON s.service_id = o.service_id
 LEFT JOIN reviews r ON o.order_id   = r.order_id
 WHERE 1=1";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND (s.title LIKE :q OR s.description LIKE :q)";
    $params[':q'] = "%{$searchQuery}%";
}
if (!empty($filterCategory) && is_numeric($filterCategory)) {
    $sql .= " AND s.category_id = :cat";
    $params[':cat'] = $filterCategory;
}
if ($minPrice !== '' && is_numeric($minPrice)) {
    $sql .= " AND s.price >= :minp";
    $params[':minp'] = $minPrice;
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= " AND s.price <= :maxp";
    $params[':maxp'] = $maxPrice;
}

$sql .= " GROUP BY s.service_id";
if ($minRating !== '' && is_numeric($minRating)) {
    $sql .= " HAVING COALESCE(AVG(r.rating),0) >= :minr";
    $params[':minr'] = $minRating;
}

$sql .= " ORDER BY s.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carregar categorias
try {
    $catStmt = $db->prepare("SELECT category_id, category_name FROM categories ORDER BY category_name");
    $catStmt->execute();
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash('Erro ao carregar categorias: ' . $e->getMessage(), 'erro');
    $categories = [];
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="page-logo">
  <img src="../uploads/Logo.png" alt="Talentum Logo">
</div>

<div class="filter-container">
  <form action="list_services.php" method="get" class="filter-form">
    <div class="form-group">
      <label for="q">Pesquisar</label>
      <input type="text" id="q" name="q" value="<?= escape($searchQuery) ?>" placeholder="Título ou descrição...">
    </div>

    <div class="form-group">
      <label for="category_id">Categoria</label>
      <select name="category_id" id="category_id">
        <option value="">Todas</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= escape($c['category_id']) ?>" <?= $filterCategory == $c['category_id'] ? 'selected' : '' ?>>
            <?= escape($c['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="min_price">Preço Mín.</label>
      <input type="number" step="0.01" id="min_price" name="min_price" value="<?= escape($minPrice) ?>">
    </div>

    <div class="form-group">
      <label for="max_price">Preço Máx.</label>
      <input type="number" step="0.01" id="max_price" name="max_price" value="<?= escape($maxPrice) ?>">
    </div>

    <div class="form-group">
      <label for="min_rating">Rating mín.</label>
      <select name="min_rating" id="min_rating">
        <option value="">—</option>
        <?php for ($r = 5; $r >= 1; $r--): ?>
          <option value="<?= $r ?>" <?= $minRating == $r ? 'selected' : '' ?>><?= $r ?>+</option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" class="button">Filtrar</button>
      <button type="button" class="button secondary" onclick="window.location='list_services.php'">Limpar</button>
    </div>
  </form>
</div>

<?php if (empty($services)): ?>
  <p style="text-align:center;">Nenhum serviço encontrado.</p>
<?php else: ?>
<div class="services-grid">
  <?php foreach ($services as $s): ?>
    <div class="service-card">
      <div class="service-image">
        <?php if (!empty($s['image'])): ?>
          <img src="../uploads/<?= escape($s['image']) ?>" alt="Imagem do serviço">
        <?php else: ?>
          <div class="image-placeholder">Sem imagem</div>
        <?php endif; ?>
      </div>

      <div class="service-info">
        <h3><?= escape($s['title']) ?></h3>
        <p class="category"><?= escape($s['category_name']) ?></p>
        <p class="freelancer">Por <strong><?= escape($s['freelancer_name']) ?></strong></p>
        <p class="price">€ <?= escape(number_format($s['price'], 2, ',', '.')) ?></p>
        <p class="rating">⭐ <?= $s['avg_rating'] ? escape(number_format($s['avg_rating'], 1)) : '—' ?></p>
        <p class="date"><?= escape(date('Y-m-d', strtotime($s['created_at']))) ?></p>

        <div class="actions">
          <a class="button" href="service.php?id=<?= escape($s['service_id']) ?>">Detalhes</a>
          <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $s['owner_id']): ?>
            <form action="../actions/hire_service_action.php" method="post" class="inline-form">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="service_id" value="<?= escape($s['service_id']) ?>">
              <button type="submit" class="button">Contratar</button>
            </form>
            <a class="button" href="messages_chat.php?user=<?= escape($s['owner_id']) ?>">Mensagem</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
