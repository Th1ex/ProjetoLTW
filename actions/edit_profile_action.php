<?php
// actions/edit_profile_action.php

// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

// Coleta os dados enviados pelo formulário
$user_id    = $_POST['user_id'] ?? '';
$name       = trim($_POST['name'] ?? '');
$username   = trim($_POST['username'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';

if (empty($user_id) || empty($name) || empty($username) || empty($email)) {
    die("Preencha todos os campos necessários.");
}

try {
    if (!empty($password)) {
        // Se a senha foi informada, atualiza com novo hash
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            UPDATE users
            SET name = :name,
                username = :username,
                email = :email,
                password_hash = :password_hash
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':name'         => $name,
            ':username'     => $username,
            ':email'        => $email,
            ':password_hash'=> $passwordHash,
            ':user_id'      => $user_id
        ]);
    } else {
        // Se o campo de senha estiver vazio, atualiza apenas os demais dados
        $stmt = $db->prepare("
            UPDATE users
            SET name = :name,
                username = :username,
                email = :email
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':name'     => $name,
            ':username' => $username,
            ':email'    => $email,
            ':user_id'  => $user_id
        ]);
    }
    
    // Opcional: atualizar os dados na sessão, se necessário
    $_SESSION['username'] = $username;
    
    // Redireciona de volta para a página de perfil
    header("Location: ../pages/profile.php");
    exit();
} catch (PDOException $e) {
    die("Erro ao atualizar perfil: " . $e->getMessage());
}
