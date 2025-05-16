<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

// Redireciona se já estiver logado
if (isset($_SESSION['user_id'])) {
    header('Location: list_services.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
?>
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Segoe UI', sans-serif;
    background-image: url('../uploads/Background.png');
    background-size: cover;
    background-position: center;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .register-container {
    background-color: rgba(0, 0, 0, 0.6);
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    max-width: 400px;
    width: 100%;
    text-align: center;
    color: #ffffff;
  }

  .register-container h2 {
    margin-bottom: 30px;
    color: #d9d6ff;
  }

  .register-container input,
  .register-container button {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
  }

  .register-container input {
    background-color: #2e2b4f;
    color: #fff;
  }
  .register-container input::placeholder {
    color: #aaa;
  }

  .register-container button {
    background-color: #7744dd;
    font-weight: bold;
    color: #fff;
    cursor: pointer;
    transition: 0.3s;
  }
  .register-container button:hover {
    background-color: #5f3dc4;
  }

  .register-container p {
    margin-top: 15px;
    font-size: 0.9rem;
  }

  .register-container a {
    color: #c2b6f3;
    text-decoration: none;
    font-weight: bold;
  }
  .register-container a:hover {
    text-decoration: underline;
  }
</style>

<div class="register-container">
  <h2>Criar Conta</h2>
  <form action="../actions/register_action.php" method="POST" class="register-form">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="text" name="name" placeholder="Nome Completo" required />
    <input type="text" name="username" placeholder="Username" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Senha" required />

    <button type="submit">Registar</button>
  </form>
  <p>Já tens conta? <a href="login.php">Entra aqui</a></p>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
