<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$receiver_id || !is_numeric($receiver_id)) {
    die("ID de destinatário inválido.");
}
if (empty($content)) {
    die("A mensagem não pode ser vazia.");
}

// Verifica se o usuário destino existe (opcional, mas recomendado)
$stmtCheck = $db->prepare("SELECT user_id FROM users WHERE user_id = :id");
$stmtCheck->execute([':id' => $receiver_id]);
$dest = $stmtCheck->fetch();
if (!$dest) {
    die("Usuário destinatário não existe.");
}

// Insere a mensagem
try {
    $stmt = $db->prepare("
        INSERT INTO messages (sender_id, receiver_id, content)
        VALUES (:sender_id, :receiver_id, :content)
    ");
    $stmt->execute([
        ':sender_id'   => $sender_id,
        ':receiver_id' => $receiver_id,
        ':content'     => $content
    ]);

    // Redireciona de volta à conversa
    header("Location: ../pages/messages_chat.php?user={$receiver_id}");
    exit();
} catch (PDOException $e) {
    die("Erro ao enviar mensagem: " . $e->getMessage());
}
