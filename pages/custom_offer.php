<?php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');

require_once __DIR__.'/../templates/header.php';
require_once __DIR__.'/../database/connection.php';

$db = getConnection();
$order_id = $_GET['order_id'] ?? '';
if (!$order_id || !is_numeric($order_id)) die('ID inválido');

// Confere se o pedido é de um serviço deste freelancer
$stmt = $db->prepare("
    SELECT o.order_id, o.status, s.title
    FROM orders o
    JOIN services s ON o.service_id = s.service_id
    WHERE o.order_id = :oid AND s.user_id = :freelancer
");
$stmt->execute([':oid'=>$order_id, ':freelancer'=>$_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) die('Pedido não encontrado');

?>

<div class="custom-offer-page">
    <h2 class="custom-offer-title">Custom Offer para pedido #<?= $order_id ?></h2>

    <form action="../actions/send_custom_offer_action.php" method="post">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">

        <label for="custom_price">Preço personalizado (€):</label>
        <input type="number" step="0.01" name="custom_price" id="custom_price" required>

        <label for="custom_delivery">Prazo personalizado (dias):</label>
        <input type="number" name="custom_delivery" id="custom_delivery" min="1" required>

        <button type="submit">Enviar oferta</button>
    </form>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
