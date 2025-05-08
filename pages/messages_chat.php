<?php
// pages/messages_chat.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

$logged_user = $_SESSION['user_id'];
$contact_id = $_GET['user'] ?? null;
if (!$contact_id || !is_numeric($contact_id)) {
    die("ID de contato inválido.");
}

// Buscar nome do contato
$stmtUser = $db->prepare("SELECT username FROM users WHERE user_id = :id");
$stmtUser->execute([':id' => $contact_id]);
$contactUser = $stmtUser->fetch();
if (!$contactUser) {
    die("Usuário não encontrado.");
}

// Buscar mensagens entre $logged_user e $contact_id
$stmtMsg = $db->prepare("
    SELECT sender_id, receiver_id, content, sent_at
    FROM messages
    WHERE 
        (sender_id = :user1 AND receiver_id = :user2)
        OR
        (sender_id = :user2 AND receiver_id = :user1)
    ORDER BY sent_at ASC
");
$stmtMsg->execute([
    ':user1' => $logged_user,
    ':user2' => $contact_id
]);
$messages = $stmtMsg->fetchAll();
?>

<div class="chat-page">
  <h2>Conversa com <?= htmlspecialchars($contactUser['username']) ?></h2>

  <div class="messages-container">
    <?php if (!$messages): ?>
      <p>Ainda não há mensagens aqui.</p>
    <?php else: ?>
      <?php foreach ($messages as $msg): 
          // Verifica se a mensagem foi enviada pelo user logado ou pelo contact
          $isSender = ($msg['sender_id'] == $logged_user);
          $side = $isSender ? "Você" : htmlspecialchars($contactUser['username']);
      ?>
        <p><strong><?= $side ?>:</strong> <?= nl2br(htmlspecialchars($msg['content'])) ?> 
          <em>(<?= $msg['sent_at'] ?>)</em></p>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Formulário para enviar nova mensagem -->
  <form action="../actions/send_message_action.php" method="post">
      <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($contact_id) ?>">
      <textarea name="content" rows="3" placeholder="Digite sua mensagem..." required></textarea>
      <br>
      <button type="submit">Enviar</button>
  </form>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
