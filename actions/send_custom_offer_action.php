<?php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: ../pages/login.php');

require_once __DIR__.'/../database/connection.php';
$db = getConnection();

$order_id       = $_POST['order_id']       ?? '';
$custom_price   = $_POST['custom_price']   ?? '';
$custom_days    = $_POST['custom_delivery']?? '';

if (!$order_id || !is_numeric($order_id))      die('ID inválido');
if (!is_numeric($custom_price) || $custom_price<=0) die('Preço inválido');
if (!is_numeric($custom_days)  || $custom_days <=0) die('Prazo inválido');

// confirma que o pedido pertence a serviço deste freelancer
$stmt = $db->prepare("
    SELECT s.user_id FROM orders o
    JOIN services s ON o.service_id = s.service_id
    WHERE o.order_id = :oid
");
$stmt->execute([':oid'=>$order_id]);
$row = $stmt->fetch();
if (!$row || $row['user_id'] != $_SESSION['user_id']) die('Sem permissão');

// Atualiza pedido com custom offer
$stmt = $db->prepare("
    UPDATE orders
    SET custom_price = :price,
        custom_delivery = :days,
        status = 'custom_offered'
    WHERE order_id = :oid
");
$stmt->execute([
    ':price'=>$custom_price,
    ':days' =>$custom_days,
    ':oid'  =>$order_id
]);

// opcional: mandar mensagem automática ao cliente avisando da oferta
header("Location: ../pages/orders_received.php");
exit();
