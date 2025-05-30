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

$orderId = sanitize($_POST['order_id'] ?? '');
$action  = sanitize($_POST['action'] ?? 'accept');  // 'accept' ou 'reject'

if (empty($orderId) || !is_numeric($orderId)) {
    flash('ID de pedido inválido.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}

try {
    $db = getConnection();

    if ($action === 'accept') {
        // Aceita oferta personalizada: aplica custom_price e custom_delivery
        $sql = "UPDATE orders
                SET total_price = custom_price,
                    custom_price = NULL,
                    custom_delivery = NULL,
                    status = 'pending'
                WHERE order_id = :id
                  AND client_id = :cli
                  AND status = 'custom_offered'";
    } else {
        // Rejeita oferta personalizada
        $sql = "UPDATE orders
                SET custom_price = NULL,
                    custom_delivery = NULL,
                    status = 'pending'
                WHERE order_id = :id
                  AND client_id = :cli
                  AND status = 'custom_offered'";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':id'  => $orderId,
        ':cli' => $_SESSION['user_id']
    ]);

    flash(
        $action === 'accept' ? 'Oferta aceite com sucesso!' : 'Oferta rejeitada.',
        'sucesso'
    );

} catch (PDOException $e) {
    flash('Erro ao processar a oferta personalizada: ' . $e->getMessage(), 'erro');
}

header('Location: ../pages/my_orders.php');
exit();
?>
