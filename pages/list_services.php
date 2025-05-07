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

<h2>Lista de Serviços</h2>

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
    <select name="min_rating">
      <option value="">—</option>
      <?php for ($r=5;$r>=1;$r--):
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
<table border="1" cellpadding="5">
  <thead>
    <tr>
      <th>Título</th><th>Preço</th><th>Miniatura</th>
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
        <?php
        /* miniatura (service_media 1.ª) ou coluna image */
        $m = $db->prepare("
           SELECT file_name, media_type
           FROM service_media WHERE service_id = ?
           ORDER BY media_id LIMIT 1
        ");
        $m->execute([$s['service_id']]);
        $thumb = $m->fetch();
        if ($thumb) {
            if ($thumb['media_type']=='image') {
                echo '<img src="../uploads/'.htmlspecialchars($thumb['file_name']).'" style="max-width:100px">';
            } else echo '🎬';
        } elseif ($s['image']) {
            echo '<img src="../uploads/'.htmlspecialchars($s['image']).'" style="max-width:100px">';
        } else echo '—';
        ?>
      </td>
      <td><?= htmlspecialchars($s['category_name']) ?></td>
      <td><?= htmlspecialchars($s['freelancer_name']) ?></td>
      <td><?= $s['avg_rating']?number_format($s['avg_rating'],1):'—' ?></td>
      <td><?= $s['created_at'] ?></td>
      <td>
        <a href="service.php?id=<?= $s['service_id'] ?>">Detalhes</a>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $s['owner_id']): ?>
          |
          <form action="../actions/hire_service_action.php"
                method="post" style="display:inline;">
            <input type="hidden" name="service_id" value="<?= $s['service_id'] ?>">
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
