<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

// Sanitizar e validar order_id
$order_id = sanitize($_GET['order_id'] ?? '');
if (empty($order_id) || !is_numeric($order_id)) {
    flash('ID de pedido inválido.', 'erro');
    header('Location: ../pages/orders_received.php');
    exit();
}

try {
    $db = getConnection();
    $stmt = $db->prepare(
        "SELECT o.order_id, o.status, s.title
         FROM orders o
         JOIN services s ON o.service_id = s.service_id
         WHERE o.order_id = :oid AND s.user_id = :freelancer"
    );
    $stmt->execute([
        ':oid' => $order_id,
        ':freelancer' => $_SESSION['user_id']
    ]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        flash('Pedido não encontrado.', 'erro');
        header('Location: ../pages/orders_received.php');
        exit();
    }
} catch (PDOException $e) {
    flash('Erro ao carregar informação do pedido: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/orders_received.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="custom-offer-page">
    <h2 class="custom-offer-title">Custom Offer para pedido #<?= escape($order_id) ?></h2>

    <form action="../actions/send_custom_offer_action.php" method="post" class="custom-offer-form">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="order_id" value="<?= escape($order_id) ?>">

        <div class="form-group">
            <label for="custom_price">Preço personalizado (€):</label>
            <input type="number" step="0.01" name="custom_price" id="custom_price" required>
        </div>

        <div class="form-group">
            <label for="custom_delivery">Prazo personalizado (dias):</label>
            <input type="number" name="custom_delivery" id="custom_delivery" min="1" required>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-primary">Enviar oferta</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
