<?php
require_once '../includes/security.php';
require_once '../includes/flash.php';
require_once '../includes/passwords.php';
require_once __DIR__ . '/../database/connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/login.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

$username_or_email = sanitize($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username_or_email) || empty($password)) {
    flash('Preenche username/email e senha.', 'erro');
    header('Location: ../pages/login.php');
    exit();
}

try {
    $db = getConnection();
    $stmt = $db->prepare(
        "SELECT user_id, username, password_hash, is_admin FROM users WHERE username = :ue OR email = :ue"
    );
    $stmt->execute([':ue' => $username_or_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash('Erro ao conectar ao sistema: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/login.php');
    exit();
}

if (!$user || !verify_password($password, $user['password_hash'])) {
    flash('Credenciais inválidas.', 'erro');
    header('Location: ../pages/login.php');
    exit();
}

// Autenticação bem-sucedida: atualizar sessão
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['is_admin'] = $user['is_admin'];

flash('Bem-vindo(a) de volta!', 'sucesso');
header('Location: ../pages/list_services.php');
exit();
?>
