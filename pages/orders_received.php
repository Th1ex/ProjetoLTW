<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();
$freelancer_id = $_SESSION['user_id'];

/* ─────── Pedidos para serviços deste freelancer ─────── */
$stmt = $db->prepare("
    SELECT 
        o.order_id,
        o.client_id,                    -- precisamos do ID do cliente
        o.status,
        o.order_date,
        o.total_price,
        s.title,
        u.username AS client_name
    FROM orders  o
    JOIN services s ON o.service_id = s.service_id
    JOIN users    u ON o.client_id  = u.user_id
    WHERE s.user_id = :freelancer_id
    ORDER BY o.order_date DESC
");
$stmt->execute([':freelancer_id' => $freelancer_id]);
$orders = $stmt->fetchAll();
?>

<h2>Pedidos Recebidos</h2>

<?php if (!$orders): ?>
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
                <td><?= htmlspecialchars($order['order_id']) ?></td>
                <td><?= htmlspecialchars($order['title'])      ?></td>
                <td><?= htmlspecialchars($order['client_name']) ?></td>
                <td><?= htmlspecialchars($order['total_price']) ?> €</td>
                <td><?= htmlspecialchars($order['status'])      ?></td>
                <td><?= htmlspecialchars($order['order_date'])  ?></td>
                <td>
                    <a href="custom_offer.php?order_id=<?= $order['order_id'] ?>">Custom Offer</a>
                    <?php if ($order['status'] === 'pending'): ?>
                        <form action="../actions/complete_order_action.php"
                              method="post" style="display:inline;">
                            <input type="hidden" name="order_id"
                                   value="<?= htmlspecialchars($order['order_id']) ?>">
                            <button type="submit">Marcar&nbsp;Concluído</button>
                        </form>
                        &nbsp;|&nbsp;
                    <?php endif; ?>

                    <!-- Link para conversar com o cliente -->
                    <a href="../pages/messages_chat.php?user=<?= $order['client_id'] ?>">
                        Mensagem&nbsp;ao&nbsp;Cliente
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
