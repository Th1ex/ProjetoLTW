<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Talentum</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../assets/js/app.js" defer></script>
</head>
<body>
<header class="main-header">
<?php if (!in_array($current_page, ['home.php', 'login.php', 'register.php', 'learnmore.php'])): ?>
    <nav class="navbar-container">
        <ul class="navbar">
            <?php if (isset($_SESSION['user_id'])):
                $stmtBal = $db->prepare("SELECT wallet FROM users WHERE user_id = :id");
                $stmtBal->execute([':id'=>$_SESSION['user_id']]);
                $saldo = $stmtBal->fetchColumn() ?: 0; ?>

                <li class="saldo">Saldo €<?= number_format($saldo, 2, ',', '.') ?></li>
                <li><a href="../pages/profile.php">Perfil</a></li>
                <li><a href="../pages/my_orders.php">Meus Pedidos</a></li>
                <li><a href="../pages/my_services.php">Meus Serviços</a></li>
                <li><a href="../pages/add_service.php">Adicionar Serviço</a></li>
                <li><a href="../pages/orders_received.php">Pedidos Recebidos</a></li>
                <li><a href="../pages/messages_inbox.php">Mensagens</a></li>
                <li><a href="../pages/list_services.php">Serviços</a></li>
                <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <li><a href="../pages/admin_panel.php">Admin</a></li>
                <?php endif; ?>
                <li>
                    <form method="post" action="../actions/logout_action.php">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit">Logout</button>
                    </form>
                </li>
            <?php else: ?>
                <li><a href="../pages/login.php">Login</a></li>
                <li><a href="../pages/register.php">Registar</a></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
</header>

<main>
    <div class="flash-messages">
        <?php foreach (get_flashes() as $flash): ?>
            <div class="flash flash-<?= escape($flash['type']) ?>"><?= escape($flash['message']) ?></div>
        <?php endforeach; ?>
    </div>