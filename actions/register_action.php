<?php
session_start();
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

$name     = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Valida se há campos obrigatórios vazios
if (empty($name) || empty($username) || empty($email) || empty($password)) {
    // Em produção, pode redirecionar de volta com mensagem de erro
    die('Preencha todos os campos obrigatórios.');
}

try {
    // Verifica se username ou email já existem
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = :username OR email = :email");
    $stmt->execute([':username' => $username, ':email' => $email]);
    $result = $stmt->fetch();

    if ($result['count'] > 0) {
        die('Username ou email já registado.');
    }

    // Gera o hash seguro da senha
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (username, password_hash, email, name) 
                          VALUES (:username, :password_hash, :email, :name)");
    $stmt->execute([
        ':username' => $username,
        ':password_hash' => $passwordHash,
        ':email' => $email,
        ':name' => $name
    ]);

    // Podes criar uma sessão de login automático após registo, se quiseres
    $user_id = $db->lastInsertId();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;

    // Redireciona para a home ou para o perfil
    header('Location: ../pages/login.php');
    exit();

} catch (PDOException $e) {
    die('Erro ao registar utilizador: ' . $e->getMessage());
}
