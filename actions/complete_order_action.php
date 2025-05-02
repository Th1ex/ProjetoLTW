<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$order_id = $_POST['order_id'] ?? null;
if (!$order_id || !is_numeric($order_id)) {
    die("ID do pedido inválido.");
}

// Verificar se o freelancer logado é o dono do serviço do pedido
$stmt = $db->prepare("
    SELECT s.user_id 
    FROM orders o 
    JOIN services s ON o.service_id = s.service_id 
    WHERE o.order_id = :order_id
");
$stmt->execute([':order_id' => $order_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    die("Pedido não encontrado.");
}

if ($pedido['user_id'] != $_SESSION['user_id']) {
    die("Você não tem permissão para marcar este pedido como concluído.");
}

// Atualiza o status do pedido para 'completed'
$stmt = $db->prepare("UPDATE orders SET status = 'completed' WHERE order_id = :order_id");
$stmt->execute([':order_id' => $order_id]);

header("Location: ../pages/orders_received.php");
exit();
?>
