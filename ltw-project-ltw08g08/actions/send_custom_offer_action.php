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

// Sanitizar inputs
$order_id      = sanitize($_POST['order_id'] ?? '');
$custom_price  = sanitize($_POST['custom_price'] ?? '');
$custom_days   = sanitize($_POST['custom_delivery'] ?? '');

// Validações
if (empty($order_id) || !is_numeric($order_id)) {
    flash('ID inválido.', 'erro');
    header('Location: ../pages/orders_received.php');
    exit();
}
if (!is_numeric($custom_price) || $custom_price <= 0) {
    flash('Preço inválido.', 'erro');
    header('Location: ../pages/orders_received.php');
    exit();
}
if (!is_numeric($custom_days) || $custom_days <= 0) {
    flash('Prazo inválido.', 'erro');
    header('Location: ../pages/orders_received.php');
    exit();
}

try {
    $db = getConnection();

    // Verifica permissão: pedido do freelancer logado
    $stmt = $db->prepare(
        "SELECT s.user_id FROM orders o JOIN services s ON o.service_id = s.service_id WHERE o.order_id = :oid"
    );
    $stmt->execute([':oid' => $order_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || $row['user_id'] != $_SESSION['user_id']) {
        flash('Sem permissão.', 'erro');
        header('Location: ../pages/orders_received.php');
        exit();
    }

    // Atualiza pedido com oferta personalizada
    $update = $db->prepare(
        "UPDATE orders SET custom_price = :price, custom_delivery = :days, status = 'custom_offered' WHERE order_id = :oid"
    );
    $update->execute([
        ':price' => $custom_price,
        ':days'  => $custom_days,
        ':oid'   => $order_id
    ]);

    flash('Oferta personalizada enviada com sucesso!', 'sucesso');
    // Opcional: adicionar lógica para notificar o cliente via mensagem ou email

} catch (PDOException $e) {
    flash('Erro ao enviar oferta personalizada: ' . $e->getMessage(), 'erro');
}

header('Location: ../pages/orders_received.php');
exit();
?>
