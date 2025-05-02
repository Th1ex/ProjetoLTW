<?php
// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
            <ul>
                <?php
                // Se o usuário estiver logado, exibe o menu completo
                if (isset($_SESSION['user_id'])) {
                    echo '<li><a href="../pages/profile.php">Perfil</a></li>';
                    echo '<li><a href="../pages/my_orders.php">Meus Pedidos</a></li>';
                    echo '<li><a href="../pages/my_services.php">Meus Serviços</a></li>';
                    echo '<li><a href="../pages/list_services.php">Serviços</a></li>';
                    echo '<li><a href="../pages/add_service.php">Adicionar Serviço</a></li>';
                    echo '<li><a href="../pages/orders_received.php">Pedidos Recebidos</a></li>';
                    echo '<li><a href="../pages/messages_inbox.php">Mensagens</a></li>';
                    // Exibe o link do painel administrativo somente se for administrador
                    if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                        echo '<li><a href="../pages/admin_panel.php">Admin</a></li>';
                    }
                    echo '<li><a href="../actions/logout_action.php">Logout</a></li>';
                } else {
                    // Se não estiver logado, exibe links para login e registro
                    echo '<li><a href="../pages/login.php">Login</a></li>';
                    echo '<li><a href="../pages/register.php">Registrar</a></li>';
                }
                ?>
            </ul>
        </nav>
    </header>
    <main>
