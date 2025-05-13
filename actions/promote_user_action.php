<?php
session_start();

/* só administradores podem promover outros */
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/profile.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../includes/flash.php';

$db = getConnection();

/* --------------------- validação do e-mail --------------------- */
$email = trim($_POST['email'] ?? '');
if ($email === '') {
    set_flash('error', 'E-mail em branco.');
    header('Location: ../pages/admin_panel.php');
    exit;
}

/* --------------------- procura utilizador --------------------- */
$stmt = $db->prepare("SELECT user_id, is_admin FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'Utilizador não encontrado.');
    header('Location: ../pages/admin_panel.php');
    exit;
}

if ($user['is_admin']) {
    set_flash('error', 'Esse utilizador já é administrador.');
    header('Location: ../pages/admin_panel.php');
    exit;
}

/* --------------------- promoção --------------------- */
$upd = $db->prepare("UPDATE users SET is_admin = 1 WHERE user_id = :uid");
$upd->execute([':uid' => $user['user_id']]);

set_flash('success', 'Utilizador promovido a administrador com sucesso!');
header('Location: ../pages/admin_panel.php');
exit;
