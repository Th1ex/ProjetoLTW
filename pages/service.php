<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

// Sanitizar e validar service_id
$service_id = sanitize($_GET['id'] ?? '');
if (empty($service_id) || !is_numeric($service_id)) {
    flash('ID inválido.', 'erro');
    header('Location: ../pages/list_services.php');
    exit();
}

try {
    $db = getConnection();

    // Busca dados do serviço
    $stmt = $db->prepare("
        SELECT s.*, c.category_name, u.username
        FROM services s
        JOIN categories c ON s.category_id = c.category_id
        JOIN users u ON s.user_id = u.user_id
        WHERE s.service_id = :sid
    ");
    $stmt->execute([':sid' => $service_id]);
    $svc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$svc) {
        throw new Exception('Serviço não encontrado.');
    }

    // Busca mídia associada
    $medStmt = $db->prepare("
        SELECT file_name, media_type
        FROM service_media
        WHERE service_id = :sid
        ORDER BY media_id
    ");
    $medStmt->execute([':sid' => $service_id]);
    $media = $medStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    flash($e->getMessage(), 'erro');
    header('Location: ../pages/list_services.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
?>

<div class="service-container">
    <h2><?= escape($svc['title']) ?></h2>
    <p><strong>Freelancer:</strong> <?= escape($svc['username']) ?></p>
    <p><strong>Categoria:</strong> <?= escape($svc['category_name']) ?></p>
    <p><strong>Preço:</strong> € <?= escape(number_format($svc['price'], 2, ',', '.')) ?></p>
    <p><strong>Entrega:</strong> <?= escape($svc['delivery_time']) ?> dias</p>

    <h3>Galeria</h3>
    <div class="slider" data-slider>
        <button class="slider-nav prev" data-prev>&larr;</button>
        <div class="slider-track">
            <?php foreach ($media as $m):
                $src = '../uploads/' . escape($m['file_name']);
                if ($m['media_type'] === 'image'): ?>
                    <img src="<?= $src ?>" class="slide" alt="Media">
                <?php else: ?>
                    <video src="<?= $src ?>" class="slide" controls></video>
                <?php endif;
            endforeach; ?>
        </div>
        <button class="slider-nav next" data-next>&rarr;</button>
    </div>

    <h3>Descrição</h3>
    <p><?= nl2br(escape($svc['description'])) ?></p>

    <?php if ($_SESSION['user_id'] != $svc['user_id']): ?>
        <form action="../actions/hire_service_action.php" method="post" class="inline-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="service_id" value="<?= escape($service_id) ?>">
            <button type="submit">Contratar</button>
        </form>
        <a href="messages_chat.php?user=<?= escape($svc['user_id']) ?>">Enviar Mensagem</a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
