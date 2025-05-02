<?php
// actions/add_category_action.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Acesso negado.");
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$category_name = trim($_POST['category_name'] ?? '');

if (empty($category_name)) {
    die("O nome da categoria não pode ser vazio.");
}

try {
    $stmt = $db->prepare("INSERT INTO categories (category_name) VALUES (:category_name)");
    $stmt->execute([':category_name' => $category_name]);
    header("Location: ../pages/admin_panel.php");
    exit();
} catch (PDOException $e) {
    die("Erro ao adicionar categoria: " . $e->getMessage());
}
?>
