<?php
require_once '../includes/security.php';
require_once '../includes/auth.php';
require_once '../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

// Apenas administradores podem criar categorias
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    flash('Acesso não autorizado.', 'erro');
    header('Location: ../pages/profile.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/admin_panel.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

// Validação do input
$name = sanitize($_POST['category_name'] ?? '');
if ($name === '') {
    flash('Nome da categoria em branco.', 'erro');
    header('Location: ../pages/admin_panel.php');
    exit();
}

try {
    $db = getConnection();

    // Verificar existência
    $stmt = $db->prepare("SELECT category_id FROM categories WHERE category_name = :n");
    $stmt->execute([':n' => $name]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        flash('Essa categoria já existe.', 'erro');
        header('Location: ../pages/admin_panel.php');
        exit();
    }

    // Inserir
    $ins = $db->prepare("INSERT INTO categories (category_name) VALUES (:n)");
    $ins->execute([':n' => $name]);

    flash('Categoria adicionada com sucesso!', 'sucesso');
} catch (PDOException $e) {
    flash('Erro ao adicionar categoria: ' . $e->getMessage(), 'erro');
}

header('Location: ../pages/admin_panel.php');
exit();
?>