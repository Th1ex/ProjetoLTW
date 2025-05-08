<?php
// pages/my_services.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

// Obtém o ID do utilizador logado
$user_id = $_SESSION['user_id'];

// Faz SELECT na tabela services, filtrando pelo user_id
$stmt = $db->prepare("
    SELECT 
        services.service_id,
        services.title,
        services.price,
        services.delivery_time,
        categories.category_name
    FROM services
    JOIN categories ON services.category_id = categories.category_id
    WHERE services.user_id = :user_id
    ORDER BY services.created_at DESC
");

$stmt->execute([':user_id' => $user_id]);
$services = $stmt->fetchAll();
?>
<div class="my-services-page">
    <h2>Meus Serviços</h2>

    <?php if (count($services) === 0): ?>
        <p>Não tens serviços cadastrados.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Entrega (dias)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars($service['title']) ?></td>
                        <td><?= htmlspecialchars($service['category_name']) ?></td>
                        <td><?= htmlspecialchars($service['price']) ?> €</td>
                        <td><?= htmlspecialchars($service['delivery_time']) ?></td>
                        <td>
                            <!-- Link para página de edição -->
                            <a href="edit_service.php?id=<?= $service['service_id'] ?>">Editar</a> 
                            |
                            <!-- Form para excluir serviço -->
                            <form action="../actions/delete_service_action.php" method="post" style="display:inline;">
                                <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                <button type="submit" onclick="return confirm('Tem certeza que deseja excluir este serviço?');">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
