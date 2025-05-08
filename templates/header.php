<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../database/connection.php';
$db = getConnection();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Talentum</title>
    <link rel="stylesheet" href="/ProjetoLTW/css/style.css">
</head>
<body>
<header class="main-header">
<?php if (!in_array($current_page, ['home.php', 'login.php', 'register.php', 'learnmore.php'])): ?>
    <nav class="navbar-container">
        <ul class="navbar">
            <?php
            if (isset($_SESSION['user_id'])) {
                $stmtBal = $db->prepare("SELECT wallet FROM users WHERE user_id = :id");
                $stmtBal->execute([':id'=>$_SESSION['user_id']]);
                $saldo = $stmtBal->fetchColumn() ?: 0;

                echo '<li class="saldo">Saldo €' . number_format($saldo, 2, ',', '.') . '</li>';
                echo '<li><a href="../pages/profile.php">Perfil</a></li>';
                echo '<li><a href="../pages/my_orders.php">Meus Pedidos</a></li>';
                echo '<li><a href="../pages/my_services.php">Meus Serviços</a></li>';
                echo '<li><a href="../pages/add_service.php">Adicionar Serviço</a></li>';
                echo '<li><a href="../pages/orders_received.php">Pedidos Recebidos</a></li>';
                echo '<li><a href="../pages/messages_inbox.php">Mensagens</a></li>';
                echo '<li><a href="../pages/list_services.php">Serviços</a></li>';

                if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                    echo '<li><a href="../pages/admin_panel.php">Admin</a></li>';
                }

                echo '<li><a href="../actions/logout_action.php">Logout</a></li>';
            } else {
                echo '<li><a href="../pages/login.php">Login</a></li>';
                echo '<li><a href="../pages/register.php">Registar</a></li>';
            }
            ?>
        </ul>
    </nav>
<?php endif; ?>
</header>

<main>


