<?php
session_start();

/* apenas administradores podem criar categorias */
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/profile.php');
    exit;
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../includes/flash.php';

$db = getConnection();

/* ------------------- validação do input ------------------- */
$name = trim($_POST['category_name'] ?? '');
if ($name === '') {
    set_flash('error', 'Nome da categoria em branco.');
    header('Location: ../pages/admin_panel.php');
    exit;
}

/* ------------------- já existe? ------------------- */
$stmt = $db->prepare("SELECT category_id FROM categories WHERE category_name = :n");
$stmt->execute([':n' => $name]);
if ($stmt->fetch()) {
    set_flash('error', 'Essa categoria já existe.');
    header('Location: ../pages/admin_panel.php');
    exit;
}

/* ------------------- inserir ------------------- */
$ins = $db->prepare("INSERT INTO categories (category_name) VALUES (:n)");
$ins->execute([':n' => $name]);

set_flash('success', 'Categoria adicionada com sucesso!');
header('Location: ../pages/admin_panel.php');
exit;
