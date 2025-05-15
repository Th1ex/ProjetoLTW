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

$order_id = sanitize($_POST['order_id'] ?? '');
if (empty($order_id) || !is_numeric($order_id)) {
    flash('ID de pedido inválido.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}

$db = getConnection();

$stmt = $db->prepare("SELECT o.status, o.total_price, s.user_id AS freelancer_id FROM orders o JOIN services s ON o.service_id = s.service_id WHERE o.order_id = :oid AND o.client_id = :cid");
$stmt->execute([
    ':oid' => $order_id,
    ':cid' => $_SESSION['user_id']
]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    flash('Pedido não encontrado ou não pertence ao usuário.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}

if ($order['status'] !== 'completed') {
    flash('Este pedido ainda não foi concluído pelo freelancer.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}

$freelancerId = $order['freelancer_id'];
$valor = $order['total_price'];

try {
    $db->beginTransaction();

    $updateOrder = $db->prepare("UPDATE orders SET status = 'closed' WHERE order_id = :oid");
    $updateOrder->execute([':oid' => $order_id]);

    $updateWallet = $db->prepare("UPDATE users SET wallet = wallet + :valor WHERE user_id = :fid");
    $updateWallet->execute([
        ':valor' => $valor,
        ':fid' => $freelancerId
    ]);

    $db->commit();

    flash('Pagamento processado e saldo do freelancer atualizado com sucesso!', 'sucesso');
    header('Location: ../pages/my_orders.php');
    exit();
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    flash('Erro na transação: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}
?>
