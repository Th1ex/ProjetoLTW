<?php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: ../pages/login.php');

require_once __DIR__.'/../database/connection.php';
$db = getConnection();

$order_id = $_POST['order_id'] ?? '';
if (!$order_id || !is_numeric($order_id)) die('ID inválido');

/* Verifica se o pedido pertence ao cliente logado e está em custom_offered */
$stmt = $db->prepare("
    SELECT status, custom_price
    FROM orders
    WHERE order_id = :oid AND client_id = :cid
");
$stmt->execute([':oid'=>$order_id, ':cid'=>$_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) die('Pedido não encontrado');
if ($order['status'] !== 'custom_offered') die('Status inválido');

/* Aceita a oferta: 
   - copia custom_price p/ total_price
   - muda status p/ in_progress */
$stmt = $db->prepare("
    UPDATE orders
    SET total_price = custom_price,
        status      = 'in_progress'
    WHERE order_id  = :oid
");
$stmt->execute([':oid'=>$order_id]);

header('Location: ../pages/my_orders.php');
exit();
