<?php
/* Inicia sessão apenas uma vez */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Conexão à base de dados (usaremos para saldo) */
require_once __DIR__ . '/../database/connection.php';
$db = getConnection();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Freelance Marketplace</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <nav>
        <ul style="list-style:none; display:flex; gap:12px; padding:0;">
            <?php
            /* ───────── Menu para utilizador autenticado ───────── */
            if (isset($_SESSION['user_id'])) {

                /* Saldo da carteira */
                $stmtBal = $db->prepare("SELECT wallet FROM users WHERE user_id = :id");
                $stmtBal->execute([':id'=>$_SESSION['user_id']]);
                $saldo = $stmtBal->fetchColumn() ?: 0;

                echo '<li style="color:green; font-weight:bold">Saldo €' .
                       number_format($saldo,2,',','.') . '</li>';

                /* Links principais */
                echo '<li><a href="../pages/profile.php">Perfil</a></li>';
                echo '<li><a href="../pages/my_orders.php">Meus Pedidos</a></li>';
                echo '<li><a href="../pages/my_services.php">Meus Serviços</a></li>';
                echo '<li><a href="../pages/add_service.php">Adicionar Serviço</a></li>';
                echo '<li><a href="../pages/orders_received.php">Pedidos Recebidos</a></li>';
                echo '<li><a href="../pages/messages_inbox.php">Mensagens</a></li>';
                echo '<li><a href="../pages/list_services.php">Serviços</a></li>';
                

                /* Link do painel de administração se for admin */
                if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                    echo '<li><a href="../pages/admin_panel.php">Admin</a></li>';
                }

                echo '<li><a href="../actions/logout_action.php">Logout</a></li>';

            /* ───────── Menu quando não logado ───────── */
            } else {
                echo '<li><a href="../pages/login.php">Login</a></li>';
                echo '<li><a href="../pages/register.php">Registar</a></li>';
            }
            ?>
        </ul>
    </nav>
</header>

<main>
