<?php
require_once '../includes/security.php';
require_once '../includes/auth.php';
require_once '../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/messages_inbox.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

$sender_id   = $_SESSION['user_id'];
$receiver_id = sanitize($_POST['receiver_id'] ?? '');
$content     = sanitize($_POST['content'] ?? '');

if (empty($receiver_id) || !is_numeric($receiver_id)) {
    flash('ID de destinatário inválido.', 'erro');
    header('Location: ../pages/messages_inbox.php');
    exit();
}

if (empty($content)) {
    flash('A mensagem não pode ser vazia.', 'erro');
    header('Location: ../pages/messages_inbox.php');
    exit();
}

try {
    $db = getConnection();

    // Verificar existência do destinatário
    $stmtCheck = $db->prepare("SELECT user_id FROM users WHERE user_id = :id");
    $stmtCheck->execute([':id' => $receiver_id]);
    if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
        flash('Usuário destinatário não existe.', 'erro');
        header('Location: ../pages/messages_inbox.php');
        exit();
    }

    // Inserir a mensagem
    $stmt = $db->prepare(
        "INSERT INTO messages (sender_id, receiver_id, content) VALUES (:sender_id, :receiver_id, :content)"
    );
    $stmt->execute([
        ':sender_id'   => $sender_id,
        ':receiver_id' => $receiver_id,
        ':content'     => $content
    ]);

    flash('Mensagem enviada com sucesso!', 'sucesso');
    header("Location: ../pages/messages_chat.php?user={$receiver_id}");
    exit();

} catch (PDOException $e) {
    flash('Erro ao enviar mensagem: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/messages_inbox.php');
    exit();
}
?>
