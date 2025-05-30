<?php
require_once '../includes/security.php';
require_once '../includes/auth.php';
require_once '../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

// Apenas administradores podem promover
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    flash('Acesso não autorizado.', 'erro');
    header('Location: ../pages/profile.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/admin_panel.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

// Sanitizar input
$email = sanitize($_POST['email'] ?? '');
if (empty($email)) {
    flash('E-mail em branco.', 'erro');
    header('Location: ../pages/admin_panel.php');
    exit();
}

try {
    $db = getConnection();

    // Procurar utilizador
    $stmt = $db->prepare("SELECT user_id, is_admin FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        flash('Utilizador não encontrado.', 'erro');
        header('Location: ../pages/admin_panel.php');
        exit();
    }

    if ($user['is_admin']) {
        flash('Esse utilizador já é administrador.', 'erro');
        header('Location: ../pages/admin_panel.php');
        exit();
    }

    // Atualizar para administrador
    $upd = $db->prepare("UPDATE users SET is_admin = 1 WHERE user_id = :uid");
    $upd->execute([':uid' => $user['user_id']]);

    flash('Utilizador promovido a administrador com sucesso!', 'sucesso');
} catch (PDOException $e) {
    flash('Erro ao promover utilizador: ' . $e->getMessage(), 'erro');
}

header('Location: ../pages/admin_panel.php');
exit();
?>