<?php
session_start();

/* ↓ ajusta o caminho se o teu connection.php estiver noutra pasta */
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../includes/flash.php';

$db = getConnection();

/* --------- obter dados do formulário --------- */
$username_or_email = trim($_POST['username'] ?? '');
$password          = $_POST['password'] ?? '';

/* --------- validação básica --------- */
if ($username_or_email === '' || $password === '') {
    set_flash('error', 'Preenche username/email e senha.');
    header('Location: ../pages/login.php');
    exit;
}

/* --------- procurar utilizador --------- */
$stmt = $db->prepare("
    SELECT user_id, username, password_hash, is_admin
    FROM users
    WHERE username = :ue OR email = :ue
");
$stmt->execute([':ue' => $username_or_email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    set_flash('error', 'Credenciais inválidas.');
    header('Location: ../pages/login.php');
    exit;
}

/* --------- sucesso: guardar sessão --------- */
$_SESSION['user_id']  = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['is_admin'] = $user['is_admin'];  

set_flash('success', 'Bem-vindo(a) de volta!');
header('Location: ../pages/list_services.php');
exit;
