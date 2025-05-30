<?php include_once '../templates/header.php'; ?>

<h2>Login</h2>
<form action="../actions/login_action.php" method="post">
  <label for="username">Username ou Email:</label>
  <input type="text" id="username" name="username" required>

  <label for="password">Senha:</label>
  <input type="password" id="password" name="password" required>

  <button type="submit">Entrar</button>
</form>

<?php include_once '../templates/footer.php'; ?>
