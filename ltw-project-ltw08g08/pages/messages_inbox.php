<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

require_login();

try {
    $db = getConnection();
    $user_id = $_SESSION['user_id'];

    // Obter lista de contactos distintos
    $stmt = $db->prepare(
        "SELECT DISTINCT
            CASE
                WHEN sender_id = :uid THEN receiver_id
                ELSE sender_id
            END AS contact_id
         FROM messages
         WHERE sender_id = :uid OR receiver_id = :uid"
    );
    $stmt->execute([':uid' => $user_id]);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    flash('Erro ao carregar conversas: ' . $e->getMessage(), 'erro');
    $contacts = [];
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="messages-inbox-page">
  <h2>Minhas Mensagens</h2>

  <?php if (empty($contacts)): ?>
    <p>Não tens conversas no momento.</p>
  <?php else: ?>
    <ul class="messages-list">
      <?php foreach ($contacts as $contact): ?>
        <?php
          $contact_id = $contact['contact_id'];
          // Obter usuário de contacto
          $stmtUser = $db->prepare("SELECT username FROM users WHERE user_id = :id");
          $stmtUser->execute([':id' => $contact_id]);
          $userInfo = $stmtUser->fetch(PDO::FETCH_ASSOC);
        ?>
        <?php if ($userInfo): ?>
          <li>
            <a href="messages_chat.php?user=<?= escape($contact_id) ?>">
              Conversar com <?= escape($userInfo['username']) ?>
            </a>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
