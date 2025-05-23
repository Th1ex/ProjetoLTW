<?php
require_once '../includes/security.php';
require_once '../includes/flash.php';
require_once '../includes/passwords.php';
require_once __DIR__ . '/../database/connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/register.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

// Sanitizar inputs
$name     = sanitize($_POST['name'] ?? '');
$username = sanitize($_POST['username'] ?? '');
$email    = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validação
if (empty($name) || empty($username) || empty($email) || empty($password)) {
    flash('Preencha todos os campos obrigatórios.', 'erro');
    header('Location: ../pages/register.php');
    exit();
}

if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
    flash('O nome de utilizador deve ter entre 3 e 20 caracteres alfanuméricos ou underscores.', 'Erro');
    header('Location: ../pages/register.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('Endereço de email inválido.', 'Erro');
    header('Location: ../pages/register.php');
    exit();
}

if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
    flash('A palavra-passe deve ter pelo menos 8 caracteres, incluindo letras e números.', 'Erro');
    header('Location: ../pages/register.php');
    exit();
}

try {
    $db = getConnection();

    // Verifica se username ou email já existem
    $stmt = $db->prepare(
        "SELECT COUNT(*) as count FROM users WHERE username = :username OR email = :email"
    );
    $stmt->execute([':username' => $username, ':email' => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        flash('Username ou email já registado.', 'erro');
        header('Location: ../pages/register.php');
        exit();
    }

    // Gera hash de password
    $passwordHash = hash_password($password);

    // Inserir utilizador
    $stmt = $db->prepare(
        "INSERT INTO users (username, password_hash, email, name) VALUES (:username, :password_hash, :email, :name)"
    );
    $stmt->execute([
        ':username'      => $username,
        ':password_hash' => $passwordHash,
        ':email'         => $email,
        ':name'          => $name
    ]);

    // Login automático após registo
    $user_id = $db->lastInsertId();
    $_SESSION['user_id']  = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['is_admin'] = 0;

    flash('Registo efetuado com sucesso! Bem-vindo(a)!', 'sucesso');
    header('Location: ../pages/list_services.php');
    exit();

} catch (PDOException $e) {
    flash('Erro ao registar utilizador: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/register.php');
    exit();
}
?>
