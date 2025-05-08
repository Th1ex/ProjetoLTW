<?php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

/* ── parâmetros de filtro ─────────────────────────────────────── */
$searchQuery    = trim($_GET['q'] ?? '');
$filterCategory =       $_GET['category_id'] ?? '';
$minPrice       =       $_GET['min_price']   ?? '';
$maxPrice       =       $_GET['max_price']   ?? '';
$minRating      =       $_GET['min_rating']  ?? '';

/* ── query base + média de rating ─────────────────────────────── */
$sql = "
 SELECT s.service_id, s.title, s.price, s.image,
        s.user_id owner_id,
        c.category_name,
        u.username freelancer_name,
        s.created_at,
        COALESCE(AVG(r.rating),0) avg_rating
 FROM services s
 JOIN categories c ON s.category_id = c.category_id
 JOIN users      u ON s.user_id     = u.user_id
 LEFT JOIN orders  o ON s.service_id = o.service_id
 LEFT JOIN reviews r ON o.order_id   = r.order_id
 WHERE 1=1";
$params = [];

/* texto */
if ($searchQuery !== '') {
    $sql .= " AND (s.title LIKE :q OR s.description LIKE :q) ";
    $params[':q'] = "%$searchQuery%";
}

/* categoria */
if ($filterCategory !== '') {
    $sql .= " AND s.category_id = :cat ";
    $params[':cat'] = $filterCategory;
}

/* preço */
if ($minPrice !== '' && is_numeric($minPrice)) {
    $sql .= " AND s.price >= :minp "; $params[':minp'] = $minPrice;
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= " AND s.price <= :maxp "; $params[':maxp'] = $maxPrice;
}

/* rating */
$sql .= " GROUP BY s.service_id ";
if ($minRating !== '' && is_numeric($minRating)) {
    $sql .= " HAVING COALESCE(AVG(r.rating),0) >= ".(float)$minRating." ";
}

$sql .= " ORDER BY s.created_at DESC";

$stmt=$db->prepare($sql);
$stmt->execute($params);
$services=$stmt->fetchAll();

/* lista de categorias para o <select> */
$categories = $db->query("
  SELECT category_id, category_name
  FROM categories ORDER BY category_name
")->fetchAll();
?>

<div class="page-logo">
  <img src="../uploads/Logo.png" alt="Talentum Logo">
</div>

<div class="filter-container">
  <form action="list_services.php" method="get" class="filter-form">
    <div class="form-group">
      <label>Pesquisar</label>
      <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Título ou descrição...">
    </div>

    <div class="form-group">
      <label>Categoria</label>
      <select name="category_id">
        <option value="">Todas</option>
        <?php foreach ($categories as $c):
              $sel = $filterCategory == $c['category_id'] ? 'selected' : ''; ?>
          <option value="<?= $c['category_id'] ?>" <?= $sel ?>>
            <?= htmlspecialchars($c['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Preço Mín.</label>
      <input type="number" step="0.01" name="min_price" value="<?= htmlspecialchars($minPrice) ?>">
    </div>

    <div class="form-group">
      <label>Preço Máx.</label>
      <input type="number" step="0.01" name="max_price" value="<?= htmlspecialchars($maxPrice) ?>">
    </div>

    <div class="form-group">
      <label>Rating mín.</label>
      <select name="min_rating">
        <option value="">—</option>
        <?php for ($r = 5; $r >= 1; $r--):
              $sel = ($minRating == $r ? 'selected' : ''); ?>
          <option value="<?= $r ?>" <?= $sel ?>><?= $r ?>+</option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" class="button">Filtrar</button>
      <button type="button" class="button secondary" onclick="window.location='list_services.php'">Limpar</button>
    </div>
  </form>
</div>

<?php if (!$services): ?>
  <p style="text-align:center;">Nenhum serviço encontrado.</p>
<?php else: ?>
<div class="services-grid">
  <?php foreach ($services as $s): ?>
    <div class="service-card">
      <div class="service-image">
        <?php if ($s['image']): ?>
          <img src="../uploads/<?= htmlspecialchars($s['image']) ?>" alt="Imagem do serviço">
        <?php else: ?>
          <div class="image-placeholder">Sem imagem</div>
        <?php endif; ?>
      </div>

      <div class="service-info">
        <h3><?= htmlspecialchars($s['title']) ?></h3>
        <p class="category"><?= htmlspecialchars($s['category_name']) ?></p>
        <p class="freelancer">Por <strong><?= htmlspecialchars($s['freelancer_name']) ?></strong></p>
        <p class="price">€ <?= htmlspecialchars($s['price']) ?></p>
        <p class="rating">⭐ <?= $s['avg_rating'] ? number_format($s['avg_rating'], 1) : '—' ?></p>
        <p class="date"><?= date('Y-m-d', strtotime($s['created_at'])) ?></p>

        <div class="actions">
          <a class="button" href="service.php?id=<?= $s['service_id'] ?>">Detalhes</a>

          <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $s['owner_id']): ?>
            <form action="../actions/hire_service_action.php" method="post" class="inline-form">
              <input type="hidden" name="service_id" value="<?= $s['service_id'] ?>">
              <button type="submit" class="button">Contratar</button>
            </form>
            <a class="button" href="messages_chat.php?user=<?= $s['owner_id'] ?>">Mensagem</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>