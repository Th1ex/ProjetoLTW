<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

require_login();

try {
    $db = getConnection();
    $client_id = $_SESSION['user_id'];

    $stmt = $db->prepare(
        "SELECT 
            o.order_id, o.service_id, o.status, o.order_date,
            o.total_price, o.custom_price, o.custom_delivery,
            s.title, s.user_id AS freelancer_id,
            u.username AS freelancer
        FROM orders o
        JOIN services s ON o.service_id = s.service_id
        JOIN users    u ON s.user_id    = u.user_id
        WHERE o.client_id = :cid
        ORDER BY o.order_date DESC"
    );
    $stmt->execute([':cid' => $client_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash('Erro ao carregar pedidos: ' . $e->getMessage(), 'erro');
    $orders = [];
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="my-orders-page">
    <h2>Meus Pedidos</h2>

    <?php if (empty($orders)): ?>
        <p>Não tens pedidos.</p>
    <?php else: ?>
    <table border="1" cellpadding="4" cellspacing="0">
    <thead>
    <tr>
        <th>ID</th><th>Serviço</th><th>Freelancer</th>
        <th>Preço</th><th>Status</th><th>Data</th><th>Ações</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <?php
            $displayPrice = $o['custom_price'] !== null ? $o['custom_price'] : $o['total_price'];
            $priceLabel   = $o['custom_price'] !== null ? ' (personalizado)' : '';
        ?>
    <tr>
        <td><?= escape($o['order_id']) ?></td>
        <td><?= escape($o['title']) ?></td>
        <td><?= escape($o['freelancer']) ?></td>
        <td>€ <?= escape(number_format($displayPrice,2,',','.')) ?><?= escape($priceLabel) ?></td>
        <td><?= escape($o['status']) ?></td>
        <td><?= escape(date('Y-m-d', strtotime($o['order_date']))) ?></td>
        <td>
            <a href="messages_chat.php?user=<?= escape($o['freelancer_id']) ?>">Mensagem</a>

            <?php if ($o['status'] === 'custom_offered'): ?>
                <span> | Oferta: €<?= escape(number_format($o['custom_price'],2,',','.')) ?> / <?= escape($o['custom_delivery']) ?>d</span>
                <form action="../actions/accept_custom_offer_action.php" method="post" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="order_id" value="<?= escape($o['order_id']) ?>">
                    <input type="hidden" name="action" value="accept">
                    <button type="submit">Aceitar</button>
                </form>
                <form action="../actions/accept_custom_offer_action.php" method="post" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="order_id" value="<?= escape($o['order_id']) ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit">Rejeitar</button>
                </form>
            <?php endif; ?>

            <?php if ($o['status'] === 'completed'): ?>
                <form action="../actions/complete_and_pay_action.php" method="post" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="order_id" value="<?= escape($o['order_id']) ?>">
                    <button type="submit">Concluir/Pagar</button>
                </form>
            <?php endif; ?>

            <?php if ($o['status'] === 'closed'): ?>
                <?php
                    $stmtRev = $db->prepare("SELECT review_id FROM reviews WHERE order_id = :oid");
                    $stmtRev->execute([':oid' => $o['order_id']]);
                ?>
                <?php if (!$stmtRev->fetch()): ?>
                    <a href="add_review.php?order_id=<?= escape($o['order_id']) ?>">Avaliar</a>
                <?php else: ?>
                    <span> | Avaliado</span>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
