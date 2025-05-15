<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

require_login();

$user_id = $_SESSION['user_id'];

try {
    $db = getConnection();
    $stmt = $db->prepare("SELECT name, username, email FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        flash('Usuário não encontrado.', 'erro');
        header('Location: ../pages/list_services.php');
        exit();
    }
} catch (PDOException $e) {
    flash('Erro ao carregar perfil: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/list_services.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="profile-page">
    <h2>Meu Perfil</h2>

    <form action="../actions/edit_profile_action.php" method="post" class="profile-form">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="user_id" value="<?= escape($user_id) ?>">

        <div class="form-group">
            <label for="name">Nome:</label>
            <input type="text" id="name" name="name" value="<?= escape($user['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= escape($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= escape($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Nova Senha (deixe em branco para manter a atual):</label>
            <input type="password" id="password" name="password">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Salvar Alterações</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
