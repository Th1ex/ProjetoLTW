<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

require_login();

try {
    $db = getConnection();
    $user_id = $_SESSION['user_id'];

    $stmt = $db->prepare(
        "SELECT 
            service_id,
            title,
            price,
            delivery_time,
            category_name
         FROM services
         JOIN categories ON services.category_id = categories.category_id
         WHERE services.user_id = :user_id
         ORDER BY services.created_at DESC"
    );
    $stmt->execute([':user_id' => $user_id]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash('Erro ao carregar serviços: ' . $e->getMessage(), 'erro');
    $services = [];
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="my-services-page">
    <h2>Meus Serviços</h2>

    <?php if (empty($services)): ?>
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
                        <td><?= escape($service['title']) ?></td>
                        <td><?= escape($service['category_name']) ?></td>
                        <td>€ <?= escape(number_format($service['price'], 2, ',', '.')) ?></td>
                        <td><?= escape($service['delivery_time']) ?></td>
                        <td>
                            <a href="edit_service.php?id=<?= escape($service['service_id']) ?>">Editar</a>
                            |
                            <form action="../actions/delete_service_action.php" method="post" class="inline-form" onsubmit="return confirm('Tem certeza que deseja excluir este serviço?');">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="service_id" value="<?= escape($service['service_id']) ?>">
                                <button type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
