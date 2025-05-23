<?php
require_once '../includes/security.php';
require_once '../includes/auth.php';
require_once '../includes/flash.php';
require_once '../includes/passwords.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/profile.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

// Sanitizar inputs
$user_id  = sanitize($_POST['user_id'] ?? '');
$name     = sanitize($_POST['name'] ?? '');
$username = sanitize($_POST['username'] ?? '');
$email    = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validações básicas
if (empty($user_id) || empty($name) || empty($username) || empty($email)) {
    flash('Preencha todos os campos necessários.', 'erro');
    header('Location: ../pages/profile.php');
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

// Verifica se o ID corresponde ao utilizador logado
if ($user_id != $_SESSION['user_id']) {
    flash('ID de utilizador inválido.', 'erro');
    header('Location: ../pages/profile.php');
    exit();
}

try {
    $db = getConnection();

    if (!empty($password)) {
        // Atualiza com nova password
        $passwordHash = hash_password($password);
        $stmt = $db->prepare(
            "UPDATE users SET name = :name, username = :username, email = :email, password_hash = :password_hash WHERE user_id = :user_id"
        );
        $stmt->execute([
            ':name'          => $name,
            ':username'      => $username,
            ':email'         => $email,
            ':password_hash' => $passwordHash,
            ':user_id'       => $user_id
        ]);
    } else {
        // Atualiza sem alterar password
        $stmt = $db->prepare(
            "UPDATE users SET name = :name, username = :username, email = :email WHERE user_id = :user_id"
        );
        $stmt->execute([
            ':name'     => $name,
            ':username' => $username,
            ':email'    => $email,
            ':user_id'  => $user_id
        ]);
    }

    // Sincroniza username na sessão
    $_SESSION['username'] = $username;

    flash('Perfil atualizado com sucesso!', 'sucesso');
    header('Location: ../pages/profile.php');
    exit();

} catch (PDOException $e) {
    flash('Erro ao atualizar perfil: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/profile.php');
    exit();
}
?>
