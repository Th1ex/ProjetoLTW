<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

// Apenas admin
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    flash('Acesso não autorizado.', 'erro');
    header('Location: ../pages/profile.php');
    exit();
}

// Sanitizar filtros de data
$from = sanitize($_GET['from'] ?? '');
$to   = sanitize($_GET['to'] ?? '');

$params = [];
$servicesDate = '';
$ordersDate = '';
$usersDate = '';

if (!empty($from)) {
    $servicesDate .= ' AND created_at >= :from';
    $ordersDate   .= ' AND order_date >= :from';
    $usersDate    .= ' AND created_at >= :from';
    $params[':from'] = $from . ' 00:00:00';
}
if (!empty($to)) {
    $servicesDate .= ' AND created_at <= :to';
    $ordersDate   .= ' AND order_date <= :to';
    $usersDate    .= ' AND created_at <= :to';
    $params[':to'] = $to . ' 23:59:59';
}

try {
    $db = getConnection();

    // Estatísticas
    $stmt = $db->prepare("SELECT COUNT(*) FROM services WHERE 1=1" . $servicesDate);
    $stmt->execute($params);
    $stats['servicos'] = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE status='closed'" . $ordersDate);
    $stmt->execute($params);
    $stats['pedidos_closed'] = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status='closed'" . $ordersDate);
    $stmt->execute($params);
    $stats['total_pago'] = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE 1=1" . $usersDate);
    $stmt->execute($params);
    $stats['novos_users'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    flash('Erro ao carregar estatísticas: ' . $e->getMessage(), 'erro');
    $stats = ['servicos'=>0,'pedidos_closed'=>0,'total_pago'=>0,'novos_users'=>0];
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="admin-panel-page">
    <h2>Painel de Administração</h2>

    <!-- filtro por data -->
    <form method="get" class="form-inline" style="margin-bottom:20px;">
        <label>De: <input type="date" name="from" value="<?= escape($from) ?>"></label>
        <label>Até: <input type="date" name="to"   value="<?= escape($to) ?>"></label>
        <button type="submit">Actualizar</button>
        <button type="button" onclick="window.location='admin_panel.php'">Limpar</button>
    </form>

    <table border="1" cellpadding="5">
        <thead>
            <tr><th>Métrica</th><th>Valor</th></tr>
        </thead>
        <tbody>
            <tr><td>Serviços publicados</td><td><?= escape($stats['servicos']) ?></td></tr>
            <tr><td>Pedidos concluídos (closed)</td><td><?= escape($stats['pedidos_closed']) ?></td></tr>
            <tr><td>Total pago a freelancers (€)</td><td><?= number_format($stats['total_pago'],2,',','.') ?></td></tr>
            <tr><td>Nº de utilizadores registados</td><td><?= escape($stats['novos_users']) ?></td></tr>
        </tbody>
    </table>

    <hr>

    <h3>Promover utilizador a administrador</h3>
    <form action="../actions/promote_user_action.php" method="post" class="form-inline" data-ajax>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <label>Email do utilizador:
            <input type="email" name="email" required>
        </label>
        <button type="submit">Promover</button>
    </form>

    <hr>

    <h3>Criar nova categoria</h3>
    <form action="../actions/add_category_action.php" method="post" class="form-inline" data-ajax>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <label>Nome da categoria:
            <input type="text" name="category_name" required>
        </label>
        <button type="submit">Adicionar</button>
    </form>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
