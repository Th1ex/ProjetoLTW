<?php
// projeto_ltw/pages/list_services.php

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

// ─────── Parâmetros de filtro via GET ──────────────────────────────────────────
$searchQuery    = trim($_GET['q']          ?? '');
$filterCategory =        $_GET['category_id'] ?? '';
$minPrice       =        $_GET['min_price']   ?? '';
$maxPrice       =        $_GET['max_price']   ?? '';

// ─────── SQL dinâmica com filtros ─────────────────────────────────────────────
$sql = "
    SELECT
        s.service_id,
        s.title,
        s.price,
        s.image,
        s.user_id      AS owner_id,
        c.category_name,
        u.username     AS freelancer_name,
        s.created_at
    FROM services s
    JOIN categories c ON s.category_id = c.category_id
    JOIN users      u ON s.user_id     = u.user_id
    WHERE 1=1
";
$params = [];

// Pesquisa por palavra-chave
if ($searchQuery !== '') {
    $sql .= " AND (s.title LIKE :search OR s.description LIKE :search) ";
    $params[':search'] = "%{$searchQuery}%";
}
// Filtro por categoria
if ($filterCategory !== '') {
    $sql .= " AND s.category_id = :category_id ";
    $params[':category_id'] = $filterCategory;
}
// Faixa de preço
if ($minPrice !== '' && is_numeric($minPrice)) {
    $sql .= " AND s.price >= :min_price ";
    $params[':min_price'] = $minPrice;
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= " AND s.price <= :max_price ";
    $params[':max_price'] = $maxPrice;
}
$sql .= " ORDER BY s.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Carrega categorias para o <select>
$stmtCat = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
$categories = $stmtCat->fetchAll();
?>

<h2>Lista de Serviços</h2>

<!-- ─────── Formulário de busca / filtros ───────────────────────────────────── -->
<form action="list_services.php" method="get" style="margin-bottom:20px;">
    <label for="q">Pesquisar:</label>
    <input type="text" id="q" name="q"
           value="<?= htmlspecialchars($searchQuery) ?>">

    <label for="category_id">Categoria:</label>
    <select name="category_id" id="category_id">
        <option value="">Todas</option>
        <?php foreach ($categories as $cat):
            $sel = ($filterCategory == $cat['category_id']) ? 'selected' : ''; ?>
            <option value="<?= $cat['category_id'] ?>" <?= $sel ?>>
                <?= htmlspecialchars($cat['category_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="min_price">Preço&nbsp;Mín.:</label>
    <input type="number" step="0.01" id="min_price"
           name="min_price" value="<?= htmlspecialchars($minPrice) ?>">

    <label for="max_price">Preço&nbsp;Máx.:</label>
    <input type="number" step="0.01" id="max_price"
           name="max_price" value="<?= htmlspecialchars($maxPrice) ?>">

    <button type="submit">Filtrar</button>
    <button type="button" onclick="window.location='list_services.php'">Limpar</button>
</form>

<?php if (!$services): ?>
    <p>Nenhum serviço encontrado.</p>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Título</th>
                <th>Preço</th>
                <th>Imagem</th>
                <th>Categoria</th>
                <th>Freelancer</th>
                <th>Data de Criação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($services as $svc): ?>
            <tr>
                <td><?= htmlspecialchars($svc['title']) ?></td>

                <td><?= htmlspecialchars($svc['price']) ?> €</td>

                <td>
                    <?php if ($svc['image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($svc['image']) ?>"
                             alt="Imagem do serviço"
                             style="max-width:120px; height:auto;">
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($svc['category_name']) ?></td>
                <td><?= htmlspecialchars($svc['freelancer_name']) ?></td>
                <td><?= htmlspecialchars($svc['created_at']) ?></td>

                <td>
                    <a href="service.php?id=<?= $svc['service_id'] ?>">Detalhes</a>

                    <?php
                    // Exibe botão “Contratar” se o user estiver logado
                    // e NÃO for o dono do serviço
                    if (isset($_SESSION['user_id']) &&
                        $_SESSION['user_id'] != $svc['owner_id']): ?>
                        |
                        <form action="../actions/hire_service_action.php"
                              method="post" style="display:inline;">
                            <input type="hidden" name="service_id"
                                   value="<?= $svc['service_id'] ?>">
                            <button type="submit">Contratar</button>
                        </form>
                    <?php endif; ?>

                    <?php
                    /* … já estamos dentro do <td> AÇÕES … */
                    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $svc['owner_id']) : ?>
                        |
                        <a href="messages_chat.php?user=<?= $svc['owner_id'] ?>">
                            Enviar&nbsp;Mensagem
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
