<?php
require_once '../includes/security.php';
require_once '../includes/auth.php';
require_once '../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/orders_received.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

$order_id = sanitize($_POST['order_id'] ?? '');
if (empty($order_id) || !is_numeric($order_id)) {
    flash('ID do pedido inválido.', 'erro');    
    header('Location: ../pages/orders_received.php');
    exit();
}

try {
    $db = getConnection();

    // Verificar se o freelancer logado é o dono do serviço do pedido
    $stmt = $db->prepare(
        "SELECT s.user_id FROM orders o JOIN services s ON o.service_id = s.service_id WHERE o.order_id = :order_id"
    );
    $stmt->execute([':order_id' => $order_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        flash('Pedido não encontrado.', 'erro');
        header('Location: ../pages/orders_received.php');
        exit();
    }

    if ($pedido['user_id'] != $_SESSION['user_id']) {
        flash('Não tens permissão para marcar este pedido como concluído.', 'erro');
        header('Location: ../pages/orders_received.php');
        exit();
    }

    // Atualizar o status do pedido para 'completed'
    $update = $db->prepare(
        "UPDATE orders SET status = 'completed' WHERE order_id = :order_id"
    );
    $update->execute([':order_id' => $order_id]);

    flash('Pedido marcado como concluído com sucesso!', 'sucesso');
    header('Location: ../pages/orders_received.php');
    exit();

} catch (PDOException $e) {
    flash('Erro ao marcar o pedido como concluído: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/orders_received.php');
    exit();
}
