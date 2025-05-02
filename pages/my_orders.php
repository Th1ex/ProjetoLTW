<?php
// pages/my_orders.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();
$client_id = $_SESSION['user_id'];

// Seleciona os pedidos feitos pelo cliente logado (orders)
// Junta com services para obter o título e delivery_time e com users para o nome do freelancer
$stmt = $db->prepare("
    SELECT 
        orders.order_id,
        orders.status,
        orders.order_date,
        orders.total_price,
        services.title,
        services.delivery_time,
        users.username AS freelancerName
    FROM orders
    JOIN services ON orders.service_id = services.service_id
    JOIN users ON services.user_id = users.user_id
    WHERE orders.client_id = :client_id
    ORDER BY orders.order_date DESC
");
$stmt->execute([':client_id' => $client_id]);
$orders = $stmt->fetchAll();
?>

<h2>Meus Pedidos</h2>

<?php if (count($orders) === 0): ?>
    <p>Não tens pedidos no momento.</p>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID do Pedido</th>
                <th>Serviço</th>
                <th>Freelancer</th>
                <th>Preço</th>
                <th>Status</th>
                <th>Data do Pedido</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                    <td><?= htmlspecialchars($order['title']) ?></td>
                    <td><?= htmlspecialchars($order['freelancerName']) ?></td>
                    <td><?= htmlspecialchars($order['total_price']) ?> €</td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td><?= htmlspecialchars($order['order_date']) ?></td>
                    <td>
                        <?php
                        // ===== INÍCIO DO CÓDIGO DO PASSO 6: AVALIAÇÃO =====
                        // Aqui implementamos a lógica para exibir o botão "Avaliar" se o pedido estiver marcado como 'completed'
                        // e ainda não tiver sido avaliado.
                        $order_id = $order['order_id'];
                        $status   = $order['status'];
                        
                        // Prepara query para verificar se já existe uma review para esse pedido
                        $stmtRev = $db->prepare("SELECT review_id FROM reviews WHERE order_id = :oid");
                        $stmtRev->execute([':oid' => $order_id]);
                        $reviewExists = $stmtRev->fetch();

                        if ($status === 'completed' && !$reviewExists) {
                            // Mostra o link para a página de avaliação
                            echo '<a href="add_review.php?order_id=' . htmlspecialchars($order_id) . '">Avaliar</a>';
                        } elseif ($reviewExists) {
                            echo '<span>Já Avaliado</span>';
                        } else {
                            echo '-';
                        }
                        // ===== FIM DO CÓDIGO DO PASSO 6 =====
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
