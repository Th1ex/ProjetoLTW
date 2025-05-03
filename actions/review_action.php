<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$order_id = $_POST['order_id'] ?? null;
$rating   = $_POST['rating'] ?? null;
$comment  = trim($_POST['comment'] ?? '');

if (!$order_id || !is_numeric($order_id)) {
    die("ID do pedido inválido.");
}
if (!$rating || !is_numeric($rating) || $rating < 1 || $rating > 5) {
    die("Nota de avaliação inválida.");
}

// Verifica se o pedido existe e pertence ao user logado, e status = completed
$stmt = $db->prepare("
    SELECT client_id, status 
    FROM orders 
    WHERE order_id = :oid
");
$stmt->execute([':oid' => $order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Pedido não encontrado.");
}
if ($order['client_id'] != $_SESSION['user_id']) {
    die("Não tens permissão para avaliar este pedido.");
}
if ($order['status'] !== 'closed') {
    die("Só é possível avaliar pedidos acabados.");
}

// Verifica se já existe review
$stmtRev = $db->prepare("SELECT review_id FROM reviews WHERE order_id = :oid");
$stmtRev->execute([':oid' => $order_id]);
if ($stmtRev->fetch()) {
    die("Este pedido já foi avaliado.");
}

// Insere a review
try {
    $stmt = $db->prepare("
        INSERT INTO reviews (order_id, rating, comment)
        VALUES (:order_id, :rating, :comment)
    ");
    $stmt->execute([
        ':order_id' => $order_id,
        ':rating'   => $rating,
        ':comment'  => $comment
    ]);

    // Redireciona de volta para "Meus Pedidos" ou onde preferires
    header('Location: ../pages/my_orders.php');
    exit();
} catch (PDOException $e) {
    die("Erro ao inserir avaliação: " . $e->getMessage());
}
