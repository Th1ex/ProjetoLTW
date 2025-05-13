<?php
session_start();
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: profile.php');   // ou página 403
    exit();
}
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

/* -------- filtros de data -------- */
$from = $_GET['from'] ?? '';
$to   = $_GET['to']   ?? '';

$servicesDate = '';   // para a tabela services
$ordersDate   = '';   // para a tabela orders (usa order_date)
$usersDate    = '';   // para a tabela users

$params = [];         // garante que existe SEMPRE

if ($from !== '') {
    $servicesDate .= " AND created_at  >= :from ";
    $ordersDate   .= " AND order_date >= :from ";
    $usersDate    .= " AND created_at >= :from ";
    $params[':from'] = $from . ' 00:00:00';
}
if ($to !== '') {
    $servicesDate .= " AND created_at  <= :to ";
    $ordersDate   .= " AND order_date <= :to ";
    $usersDate    .= " AND created_at <= :to ";
    $params[':to'] = $to . ' 23:59:59';
}

/* -------- estatísticas -------- */
$stats = [];

/* serviços publicados */
$stmt = $db->prepare("SELECT COUNT(*) FROM services WHERE 1=1 $servicesDate");
$stmt->execute($params);
$stats['servicos'] = $stmt->fetchColumn();

/* pedidos concluídos */
$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE status='closed' $ordersDate");
$stmt->execute($params);
$stats['pedidos_closed'] = $stmt->fetchColumn();

/* total pago aos freelancers (€) */
$stmt = $db->prepare("SELECT COALESCE(SUM(total_price),0)
                      FROM orders WHERE status='closed' $ordersDate");
$stmt->execute($params);
$stats['total_pago'] = $stmt->fetchColumn();

/* novos utilizadores registados */
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE 1=1 $usersDate");
$stmt->execute($params);
$stats['novos_users'] = $stmt->fetchColumn();

?>

<div class="admin-panel-page">
    <h2>Painel de Administração</h2>

    <!-- filtro por data -->
    <form method="get" style="margin-bottom:20px;">
        <label>De: <input type="date" name="from" value="<?= htmlspecialchars($from) ?>"></label>
        <label>Até: <input type="date" name="to"   value="<?= htmlspecialchars($to) ?>"></label>
        <button type="submit">Actualizar</button>
        <button type="button" onclick="window.location='admin_panel.php'">Limpar</button>
    </form>

    <table border="1" cellpadding="5">
    <tr><th>Métrica</th><th>Valor</th></tr>
    <tr><td>Serviços publicados</td><td><?= $stats['servicos'] ?></td></tr>
    <tr><td>Pedidos concluídos (closed)</td><td><?= $stats['pedidos_closed'] ?></td></tr>
    <tr><td>Total pago a freelancers (€)</td><td><?= number_format($stats['total_pago'],2,',','.') ?></td></tr>
    <tr><td>Nº de utilizadores registados</td><td><?= $stats['novos_users'] ?></td></tr>
    </table>

    <hr>

    <h3>Promover utilizador a administrador</h3>
    <form action="../actions/promote_user_action.php" method="post">
        <label>Email do utilizador:
            <input type="email" name="email" required>
        </label>
        <button type="submit">Promover</button>
    </form>

    <hr>

    <h3>Criar nova categoria</h3>
    <form action="../actions/add_category_action.php" method="post">
        <label>Nome da categoria:
            <input type="text" name="category_name" required>
        </label>
        <button type="submit">Adicionar</button>
    </form>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
