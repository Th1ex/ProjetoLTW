<?php include_once '../templates/header.php'; ?>

<h2>Criar Conta</h2>
<form action="../actions/register_action.php" method="post">
  <label for="name">Nome Completo:</label>
  <input type="text" id="name" name="name" required>

  <label for="username">Username:</label>
  <input type="text" id="username" name="username" required>

  <label for="email">Email:</label>
  <input type="email" id="email" name="email" required>

  <label for="password">Senha:</label>
  <input type="password" id="password" name="password" required>

  <button type="submit">Registar</button>
</form>

<?php include_once '../templates/footer.php'; ?>
