<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();
$client_id = $_SESSION['user_id'];

/* pedidos do cliente */
$stmt = $db->prepare("
    SELECT 
        o.order_id, o.service_id, o.status, o.order_date,
        o.total_price, o.custom_price, o.custom_delivery,
        s.title, s.user_id AS freelancer_id,
        u.username AS freelancer
    FROM orders o
    JOIN services s ON o.service_id = s.service_id
    JOIN users    u ON s.user_id    = u.user_id
    WHERE o.client_id = :cid
    ORDER BY o.order_date DESC
");
$stmt->execute([':cid'=>$client_id]);
$orders = $stmt->fetchAll();
?>
<div class="my-orders-page">
    <h2>Meus Pedidos</h2>

    <?php if (!$orders): ?>
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
        <td><?= $o['order_id'] ?></td>
        <td><?= htmlspecialchars($o['title']) ?></td>
        <td><?= htmlspecialchars($o['freelancer']) ?></td>
        <td><?= $displayPrice ?> €<?= $priceLabel ?></td>
        <td><?= $o['status'] ?></td>
        <td><?= $o['order_date'] ?></td>
        <td>
            <!-- link Mensagem (sempre) -->
            <a href="../pages/messages_chat.php?user=<?= $o['freelancer_id'] ?>">Mensagem</a>

            <?php
            /* ---------- Ações específicas por estado ---------- */

            /* oferta personalizada */
            if ($o['status'] === 'custom_offered') {
                echo ' | Oferta: '.$o['custom_price'].' € / '.$o['custom_delivery'].' d&nbsp;';
                    // aceitar
                ?>  <form action="../actions/accept_custom_offer_action.php"
                        method="post" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                        <input type="hidden" name="action" value="accept">
                        <button type="submit">Aceitar</button>
                    </form>

                    <!-- REJEITAR -->
                    <form action="../actions/accept_custom_offer_action.php"
                        method="post" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit">Rejeitar</button>
                    </form>
                <?php
                }

            /* concluir & pagar (quando já completed pelo freelancer) */
            if ($o['status'] === 'completed') {
                echo ' | ';
                ?>
                <form action="../actions/complete_and_pay_action.php" method="post" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                    <button type="submit">Concluir&nbsp;/&nbsp;Pagar</button>
                </form>
            <?php
            }

            /* ★ Avaliação – depois que o pedido está fechado e ainda não foi avaliado ★ */
            if ($o['status'] === 'closed') {
                /* verifica se já existe review */
                $stmtRev = $db->prepare("SELECT review_id FROM reviews WHERE order_id = :oid");
                $stmtRev->execute([':oid' => $o['order_id']]);
                if (!$stmtRev->fetch()) {
                    echo ' | ';
                    ?>
                    <a href="add_review.php?order_id=<?= $o['order_id'] ?>">Avaliar</a>
                    <?php
                } else {
                    echo ' | Avaliado';
                }
            }
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
