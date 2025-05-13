<?php
session_start();
require_once __DIR__.'/../database/connection.php';
$db = getConnection();

$orderId = (int)$_POST['order_id'];
$client  = $_SESSION['user_id'];
$action  = $_POST['action'] ?? 'accept';   // ‘accept’ ou ‘reject’

if ($action === 'accept') {
    // copia o preço da oferta, volta a pending
    $sql = "UPDATE orders
            SET total_price = custom_price,
                custom_price = NULL,
                custom_delivery = NULL,
                status = 'pending'
            WHERE order_id = :id
              AND client_id = :cli
              AND status = 'custom_offered'";
} else {            // reject
    $sql = "UPDATE orders
            SET custom_price = NULL,
                custom_delivery = NULL,
                status = 'pending'
            WHERE order_id = :id
              AND client_id = :cli
              AND status = 'custom_offered'";
}

$stmt = $db->prepare($sql);
$stmt->execute([':id'=>$orderId, ':cli'=>$client]);

header('Location: ../pages/my_orders.php');
exit();
