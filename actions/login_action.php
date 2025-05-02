<?php
session_start();
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

$username_or_email = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validação simples de campos
if (empty($username_or_email) || empty($password)) {
    die('Preencha username/email e senha.');
}

// Seleciona o usuário incluindo o campo is_admin
$stmt = $db->prepare("
    SELECT user_id, username, password_hash, is_admin
    FROM users
    WHERE username = :ue OR email = :ue
");
$stmt->execute([':ue' => $username_or_email]);
$user = $stmt->fetch();

if (!$user) {
    die("Credenciais inválidas (utilizador não encontrado).");
}

// Verifica se a senha é correta
if (password_verify($password, $user['password_hash'])) {
    // Salva as informações na sessão
    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = $user['is_admin'];  // Armazena o valor de is_admin

    header('Location: ../pages/home.php');
    exit();
} else {
    die("Credenciais inválidas (senha incorreta).");
}
