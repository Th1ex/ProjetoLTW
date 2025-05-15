<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

require_login();

try {
    $db = getConnection();
    $freelancer_id = $_SESSION['user_id'];
    
    // Pedidos para serviços deste freelancer
    $stmt = $db->prepare(
        "SELECT 
            o.order_id,
            o.client_id,
            o.status,
            o.order_date,
            o.total_price,
            s.title,
            u.username AS client_name
         FROM orders o
         JOIN services s ON o.service_id = s.service_id
         JOIN users    u ON o.client_id  = u.user_id
         WHERE s.user_id = :freelancer_id
         ORDER BY o.order_date DESC"
    );
    $stmt->execute([':freelancer_id' => $freelancer_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash('Erro ao carregar pedidos recebidos: ' . $e->getMessage(), 'erro');
    $orders = [];
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="orders-received-page">
    <h2>Pedidos Recebidos</h2>

    <?php if (empty($orders)): ?>
        <p>Não há pedidos recebidos no momento.</p>
    <?php else: ?>
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Serviço</th>
                    <th>Cliente</th>
                    <th>Preço</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= escape($order['order_id']) ?></td>
                    <td><?= escape($order['title']) ?></td>
                    <td><?= escape($order['client_name']) ?></td>
                    <td>€ <?= escape(number_format($order['total_price'], 2, ',', '.')) ?></td>
                    <td><?= escape($order['status']) ?></td>
                    <td><?= escape(date('Y-m-d', strtotime($order['order_date']))) ?></td>
                    <td>
                        <a href="custom_offer.php?order_id=<?= escape($order['order_id']) ?>">Custom Offer</a>

                        <?php if ($order['status'] === 'pending'): ?>
                            <form action="../actions/complete_order_action.php" method="post" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="order_id" value="<?= escape($order['order_id']) ?>">
                                <button type="submit">Marcar Concluído</button>
                            </form>
                            &nbsp;|&nbsp;
                        <?php endif; ?>

                        <a href="../pages/messages_chat.php?user=<?= escape($order['client_id']) ?>">Mensagem ao Cliente</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
