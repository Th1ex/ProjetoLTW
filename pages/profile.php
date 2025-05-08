<?php
// pages/profile.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT name, username, email FROM users WHERE user_id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p>Usuário não encontrado.</p>";
    require_once __DIR__ . '/../templates/footer.php';
    exit();
}
?>

<div class="profile-page">
    <h2>Meu Perfil</h2>

    <form action="../actions/edit_profile_action.php" method="post">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">

        <div class="form-group">
            <label for="name">Nome:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Nova Senha (deixe em branco para manter a atual):</label>
            <input type="password" id="password" name="password">
        </div>

        <button type="submit">Salvar Alterações</button>
    </form>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
