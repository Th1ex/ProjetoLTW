<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

require_login();

// Sanitizar contact_id
$contact_id = sanitize($_GET['user'] ?? '');
if (empty($contact_id) || !is_numeric($contact_id)) {
    flash('ID de contato inválido.', 'erro');
    header('Location: ../pages/messages_inbox.php');
    exit();
}

try {
    $db = getConnection();

    // Obter nome do contato
    $stmtUser = $db->prepare("SELECT username FROM users WHERE user_id = :id");
    $stmtUser->execute([':id' => $contact_id]);
    $contactUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$contactUser) {
        flash('Usuário não encontrado.', 'erro');
        header('Location: ../pages/messages_inbox.php');
        exit();
    }

    // Carregar mensagens
    $stmtMsg = $db->prepare(
        "SELECT sender_id, receiver_id, content, sent_at
         FROM messages
         WHERE (sender_id = :user1 AND receiver_id = :user2)
            OR (sender_id = :user2 AND receiver_id = :user1)
         ORDER BY sent_at ASC"
    );
    $stmtMsg->execute([
        ':user1' => $_SESSION['user_id'],
        ':user2' => $contact_id
    ]);
    $messages = $stmtMsg->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    flash('Erro ao carregar mensagens: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/messages_inbox.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="chat-page">
  <h2>Conversa com <?= escape($contactUser['username']) ?></h2>

  <div class="messages-container">
    <?php if (empty($messages)): ?>
      <p>Ainda não há mensagens aqui.</p>
    <?php else: ?>
      <?php foreach ($messages as $msg): ?>
        <?php
          $isSender = ($msg['sender_id'] == $_SESSION['user_id']);
          $sender = $isSender ? 'Você' : escape($contactUser['username']);
        ?>
        <div class="message <?= $isSender ? 'sent' : 'received' ?>">
          <p><strong><?= $sender ?>:</strong> <?= nl2br(escape($msg['content'])) ?></p>
          <span class="timestamp"><?= escape($msg['sent_at']) ?></span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <form action="../actions/send_message_action.php" method="post" class="message-form">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="receiver_id" value="<?= escape($contact_id) ?>">
      <textarea name="content" rows="3" placeholder="Digite sua mensagem..." required></textarea>
      <button type="submit">Enviar</button>
  </form>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
