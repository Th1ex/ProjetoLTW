<?php
session_start();
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/profile.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$email = trim($_POST['email'] ?? '');
if ($email === '') {
    die('Email em branco.');
}

/* procura utilizador */
$stmt = $db->prepare("SELECT user_id, is_admin FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    die('Utilizador não encontrado.');
}

if ($user['is_admin'] == 1) {
    die('Utilizador já é administrador.');
}

/* actualiza */
$stmt = $db->prepare("UPDATE users SET is_admin = 1 WHERE user_id = :uid");
$stmt->execute([':uid' => $user['user_id']]);

echo 'Utilizador promovido a admin com sucesso.';
echo '<br><a href="../pages/admin_panel.php">Voltar ao painel</a>';
