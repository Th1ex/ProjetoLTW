<?php
session_start();
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

$username_or_email = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username_or_email) || empty($password)) {
    die('Preencha username/email e senha.');
}

try {
    // Procura o usuário pelo username ou email
    $stmt = $db->prepare(
        "SELECT user_id, username, password_hash 
         FROM users 
         WHERE username = :ue OR email = :ue"
    );
    $stmt->execute([':ue' => $username_or_email]);
    $user = $stmt->fetch();

    if (!$user) {
        die('Credenciais inválidas (utilizador não encontrado).');
    }

    // Verifica a senha
    if (password_verify($password, $user['password_hash'])) {
        // Guarda dados de sessão
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        header('Location: ../pages/home.php');
        exit();
    } else {
        die('Credenciais inválidas (senha incorreta).');
    }
} catch (PDOException $e) {
    die('Erro ao processar login: ' . $e->getMessage());
}
