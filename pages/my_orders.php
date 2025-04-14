<?php
// pages/my_orders.php

session_start();
// Exigir login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

// Obtém o ID do utilizador logado
$client_id = $_SESSION['user_id'];

// Busca todos os pedidos do cliente logado
// Juntamos com 'services' para obter informações do serviço
// e com 'users' para saber o nome do freelancer que oferece o serviço
$stmt = $db->prepare("
    SELECT 
        orders.order_id,
        orders.status,
        orders.order_date,
        orders.total_price,
        services.title,
        services.description,
        services.delivery_time,
        users.username AS freelancerName
    FROM orders
    JOIN services ON orders.service_id = services.service_id
    JOIN users ON services.user_id = users.user_id  -- aqui pega o freelancer
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
    <table>
        <thead>
            <tr>
                <th>#ID</th>
                <th>Serviço</th>
                <th>Freelancer</th>
                <th>Preço</th>
                <th>Status</th>
                <th>Data</th>
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
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
