<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Freelance Marketplace</title>
  <link rel="stylesheet" href="../css/style.css"> <!-- Ajustar caminho conforme a estrutura -->
</head>
<body>
  <header>
    <nav>
      <ul>
        <li><a href="../pages/home.php">Home</a></li>
        <li><a href="../pages/list_services.php">Serviços</a></li>
        <!-- Se o utilizador estiver logado, mostra link de perfil, senão mostra login/registro -->
        <?php
          session_start();
          if (isset($_SESSION['user_id'])) {
            echo '<li><a href="../pages/profile.php">Perfil</a></li>';
            echo '<li><a href="../pages/my_orders.php">Meus Pedidos</a></li>';
            echo '<li><a href="../actions/logout_action.php">Logout</a></li>';
            echo '<li><a href="../pages/my_services.php">Meus Serviços</a></li>';
          } else {
              echo '<li><a href="../pages/login.php">Login</a></li>';
              echo '<li><a href="../pages/register.php">Registar</a></li>';
          }
        ?>
      </ul>
    </nav>
  </header>
  <main>
