<?php
// pages/messages_inbox.php A ideia é exibir todos os utilizadores com quem o utilizador logado trocou ou recebeu mensagens. 
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

$user_id = $_SESSION['user_id'];

// Buscamos conversas onde este user é sender ou receiver. 
// Isto retorna todas as mensagens; para agrupar por user, podemos usar DISTINCT ou GROUP BY.
$stmt = $db->prepare("
    SELECT DISTINCT
        CASE 
            WHEN sender_id = :user_id THEN receiver_id
            ELSE sender_id
        END AS contact_id
    FROM messages
    WHERE sender_id = :user_id OR receiver_id = :user_id
");
$stmt->execute([':user_id' => $user_id]);
$contacts = $stmt->fetchAll();
?>

<div class="messages-inbox-page">
  <h2>Minhas Mensagens</h2>

  <?php if (count($contacts) === 0): ?>
    <p>Não tens conversas no momento.</p>
  <?php else: ?>
    <ul class="messages-list">
      <?php 
      foreach ($contacts as $contact) {
          // Identifica o outro utilizador nessa conversa
          $contact_id = $contact['contact_id'];

          // Buscar nome do contacto
          $stmtUser = $db->prepare("SELECT username FROM users WHERE user_id = :id");
          $stmtUser->execute([':id' => $contact_id]);
          $userInfo = $stmtUser->fetch();

          if ($userInfo) {
              $contactUsername = htmlspecialchars($userInfo['username']);
              echo "<li><a href=\"messages_chat.php?user={$contact_id}\">Conversar com {$contactUsername}</a></li>";
          }
      }
      ?>
    </ul>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
