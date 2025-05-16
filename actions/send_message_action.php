<?php
session_start();
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

// CSRF check com suporte a AJAX
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    $error = 'Token CSRF inválido.';
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $error]);
        exit();
    }
    die($error);
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$sender_id   = $_SESSION['user_id'];
$receiver_id = filter_var($_POST['receiver_id'], FILTER_VALIDATE_INT);
$content     = trim($_POST['content'] ?? '');

if (!$receiver_id) {
    $error = 'ID de destinatário inválido.';
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $error]);
        exit();
    }
    die($error);
}
if (empty($content)) {
    $error = 'A mensagem não pode ser vazia.';
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $error]);
        exit();
    }
    die($error);
}

// Verifica se o usuário destino existe
$stmtCheck = $db->prepare("SELECT user_id FROM users WHERE user_id = :id");
$stmtCheck->execute([':id' => $receiver_id]);
if (!$stmtCheck->fetch()) {
    $error = 'Usuário destinatário não existe.';
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $error]);
        exit();
    }
    die($error);
}

try {
    $stmt = $db->prepare(
        "INSERT INTO messages (sender_id, receiver_id, content) VALUES (:sender_id, :receiver_id, :content)"
    );
    $stmt->execute([
        ':sender_id'   => $sender_id,
        ':receiver_id' => $receiver_id,
        ':content'     => $content
    ]);
    $sent_at = date('Y-m-d H:i:s');

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'content' => $content, 'sent_at' => $sent_at]);
        exit();
    }

    header("Location: ../pages/messages_chat.php?user={$receiver_id}");
    exit();
} catch (PDOException $e) {
    $error = 'Erro ao enviar mensagem: ' . $e->getMessage();
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $error]);
        exit();
    }
    die($error);
}
