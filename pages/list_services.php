<?php
// pages/list_services.php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

/* ── parâmetros de filtro ───────────────────────────────────────── */
$searchQuery    = trim($_GET['q']          ?? '');
$filterCategory =        $_GET['category_id'] ?? '';
$minPrice       =        $_GET['min_price']   ?? '';
$maxPrice       =        $_GET['max_price']   ?? '';
$minRating      =        $_GET['min_rating']  ?? '';   // novo filtro

/* ── query base com média de rating ─────────────────────────────── */
$sql = "
 SELECT
   s.service_id,
   s.title,
   s.price,
   s.image,
   s.user_id         AS owner_id,
   c.category_name,
   u.username        AS freelancer_name,
   s.created_at,
   COALESCE(AVG(r.rating),0) AS avg_rating
 FROM services s
 JOIN categories c ON s.category_id = c.category_id
 JOIN users      u ON s.user_id     = u.user_id
 LEFT JOIN orders  o ON s.service_id = o.service_id
 LEFT JOIN reviews r ON o.order_id   = r.order_id
 WHERE 1=1
";
$params = [];

/* ── filtros texto / categoria / preço ──────────────────────────── */
if ($searchQuery !== '') {
    $sql .= " AND (s.title LIKE :search OR s.description LIKE :search) ";
    $params[':search'] = "%{$searchQuery}%";
}
if ($filterCategory !== '') {
    $sql .= " AND s.category_id = :cat ";
    $params[':cat'] = $filterCategory;
}
if ($minPrice !== '' && is_numeric($minPrice)) {
    $sql .= " AND s.price >= :minp ";
    $params[':minp'] = $minPrice;
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= " AND s.price <= :maxp ";
    $params[':maxp'] = $maxPrice;
}

/* ── agrupa e (opcional) filtra por rating ─────────────────────── */
$sql .= " GROUP BY s.service_id ";

if ($minRating !== '' && is_numeric($minRating)) {
    /* Insere valor numérico diretamente no HAVING */
    $valorMin = (float)$minRating;
    $sql .= " HAVING COALESCE(AVG(r.rating),0) >= $valorMin ";
}

$sql .= " ORDER BY s.created_at DESC";

/* ── executa ────────────────────────────────────────────────────── */
$stmt = $db->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

/* ── carrega categorias para o <select> ─────────────────────────── */
$categories = $db->query("
   SELECT category_id, category_name
   FROM categories
   ORDER BY category_name
")->fetchAll();
?>

<h2>Lista de Serviços</h2>

<!-- ── formulário de filtros ────────────────────────────────────── -->
<form action="list_services.php" method="get" style="margin-bottom:20px;">
  <label>Pesquisar:
    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
  </label>

  <label>Categoria:
    <select name="category_id">
      <option value="">Todas</option>
      <?php foreach ($categories as $c):
            $sel = $filterCategory == $c['category_id'] ? 'selected' : ''; ?>
        <option value="<?= $c['category_id'] ?>" <?= $sel ?>>
          <?= htmlspecialchars($c['category_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>Preço&nbsp;Mín.:
    <input type="number" step="0.01" name="min_price"
           value="<?= htmlspecialchars($minPrice) ?>">
  </label>

  <label>Preço&nbsp;Máx.:
    <input type="number" step="0.01" name="max_price"
           value="<?= htmlspecialchars($maxPrice) ?>">
  </label>

  <label>Rating&nbsp;mín.:
    <select name="min_rating" id="min_rating">
      <option value="">—</option>
      <?php for ($r = 5; $r >= 1; $r--):
            $sel = ($minRating == $r ? 'selected':''); ?>
        <option value="<?= $r ?>" <?= $sel ?>><?= $r ?>+</option>
      <?php endfor; ?>
    </select>
  </label>

  <button type="submit">Filtrar</button>
  <button type="button" onclick="window.location='list_services.php'">Limpar</button>
</form>

<?php if (!$services): ?>
  <p>Nenhum serviço encontrado.</p>
<?php else: ?>
<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr>
      <th>Título</th><th>Preço</th><th>Imagem</th>
      <th>Categoria</th><th>Freelancer</th><th>Rating</th>
      <th>Data</th><th>Ações</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($services as $s): ?>
    <tr>
      <td><?= htmlspecialchars($s['title']) ?></td>
      <td><?= htmlspecialchars($s['price']) ?> €</td>
      <td>
        <?php if ($s['image']): ?>
          <img src="../uploads/<?= htmlspecialchars($s['image']) ?>" style="max-width:120px">
        <?php else: ?> — <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($s['category_name']) ?></td>
      <td><?= htmlspecialchars($s['freelancer_name']) ?></td>
      <td><?= $s['avg_rating'] ? number_format($s['avg_rating'],1) : '—' ?></td>
      <td><?= $s['created_at'] ?></td>
      <td>
        <a href="service.php?id=<?= $s['service_id'] ?>">Detalhes</a>
        <?php if (isset($_SESSION['user_id']) &&
                  $_SESSION['user_id'] != $s['owner_id']): ?>
          |
          <form action="../actions/hire_service_action.php"
                method="post" style="display:inline;">
            <input type="hidden" name="service_id"
                   value="<?= $s['service_id'] ?>">
            <button type="submit">Contratar</button>
          </form>
          |
          <a href="messages_chat.php?user=<?= $s['owner_id'] ?>">Mensagem</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
