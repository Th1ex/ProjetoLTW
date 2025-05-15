<?php
require_once '../includes/security.php';
require_once '../includes/auth.php';
require_once '../includes/flash.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/list_services.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

// Encerra sessão do utilizador
session_unset();
session_destroy();

flash('Sessão terminada com sucesso.', 'sucesso');
header('Location: ../pages/login.php');
exit();
?>