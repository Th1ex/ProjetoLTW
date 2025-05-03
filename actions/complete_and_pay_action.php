<?php
// actions/complete_and_pay_action.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$order_id = $_POST['order_id'] ?? null;
if (!$order_id || !is_numeric($order_id)) {
    die('ID de pedido inválido.');
}

/* Busca pedido + freelancer + valor, garantindo que pertence ao cliente logado */
$stmt = $db->prepare("
    SELECT 
        o.status,
        o.total_price,
        s.user_id AS freelancer_id
    FROM orders o
    JOIN services s ON o.service_id = s.service_id
    WHERE o.order_id = :oid
      AND o.client_id = :cid
");
$stmt->execute([
    ':oid' => $order_id,
    ':cid' => $_SESSION['user_id']
]);
$order = $stmt->fetch();

if (!$order) {
    die('Pedido não encontrado ou não pertence ao usuário.');
}

if ($order['status'] !== 'completed') {
    die('Este pedido ainda não foi concluído pelo freelancer.');
}

$freelancerId = $order['freelancer_id'];
$valor        = $order['total_price'];

/* Transação: 1) muda status para 'closed'; 2) credita carteira do freelancer */
try {
    $db->beginTransaction();

    /* 1. Atualiza status */
    $db->prepare("
        UPDATE orders
        SET status = 'closed'
        WHERE order_id = :oid
    ")->execute([':oid' => $order_id]);

    /* 2. Credita saldo */
    $db->prepare("
        UPDATE users
        SET wallet = wallet + :valor
        WHERE user_id = :fid
    ")->execute([
        ':valor' => $valor,
        ':fid'   => $freelancerId
    ]);

    $db->commit();

} catch (PDOException $e) {
    $db->rollBack();
    die('Erro na transação: ' . $e->getMessage());
}

header('Location: ../pages/my_orders.php');
exit();
