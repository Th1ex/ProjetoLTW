<?php
require_once '../includes/security.php';
require_once '../includes/auth.php';
require_once '../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

// Sanitizar inputs
$order_id = sanitize($_POST['order_id'] ?? '');
$rating   = sanitize($_POST['rating'] ?? '');
$comment  = sanitize($_POST['comment'] ?? '');

// Validações
if (empty($order_id) || !is_numeric($order_id)) {
    flash('ID do pedido inválido.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}
if (empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
    flash('Nota de avaliação inválida.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}

try {
    $db = getConnection();

    // Verificar pedido
    $stmt = $db->prepare(
        "SELECT client_id, status FROM orders WHERE order_id = :oid"
    );
    $stmt->execute([':oid' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        flash('Pedido não encontrado.', 'erro');
        header('Location: ../pages/my_orders.php');
        exit();
    }
    if ($order['client_id'] != $_SESSION['user_id']) {
        flash('Não tens permissão para avaliar este pedido.', 'erro');
        header('Location: ../pages/my_orders.php');
        exit();
    }
    if ($order['status'] !== 'closed') {
        flash('Só é possível avaliar pedidos acabados.', 'erro');
        header('Location: ../pages/my_orders.php');
        exit();
    }

    // Verificar existência de review
    $stmtRev = $db->prepare(
        "SELECT review_id FROM reviews WHERE order_id = :oid"
    );
    $stmtRev->execute([':oid' => $order_id]);
    if ($stmtRev->fetch(PDO::FETCH_ASSOC)) {
        flash('Este pedido já foi avaliado.', 'erro');
        header('Location: ../pages/my_orders.php');
        exit();
    }

    // Inserir avaliação
    $stmtIns = $db->prepare(
        "INSERT INTO reviews (order_id, rating, comment) VALUES (:order_id, :rating, :comment)"
    );
    $stmtIns->execute([
        ':order_id' => $order_id,
        ':rating'   => $rating,
        ':comment'  => $comment
    ]);

    flash('Avaliação inserida com sucesso!', 'sucesso');
    header('Location: ../pages/my_orders.php');
    exit();

} catch (PDOException $e) {
    flash('Erro ao inserir avaliação: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}
?>
