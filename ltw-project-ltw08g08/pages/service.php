<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

    // Buscar dados do serviço
    $stmt = $db->prepare("
        SELECT s.*, c.category_name, u.username
        FROM services s
        JOIN categories c ON s.category_id = c.category_id
        JOIN users u ON s.user_id = u.user_id
        WHERE s.service_id = :sid
    ");
    $stmt->execute([':sid' => $service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        throw new Exception('Serviço não encontrado.');
    }

    // Buscar mídia associada
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
    <div class="service-header">
        <h1 class="service-title"><?= escape($service['title']) ?></h1>
        <p class="service-category"><?= escape($service['category_name']) ?></p>
    </div>

    <div class="service-content">
        <div class="service-info">
            <p><strong>Freelancer:</strong> <?= escape($service['username']) ?></p>
            <p><strong>Preço:</strong> € <?= escape(number_format($service['price'], 2, ',', '.')) ?></p>
            <p><strong>Entrega:</strong> <?= escape($service['delivery_time']) ?> dias</p>

            <h3 class="description-title">Descrição</h3>
            <p class="service-description"><?= nl2br(escape($service['description'])) ?></p>
        </div>


        <?php if (!empty($media)): ?>
        <div class="media-gallery">
            <div class="slider" data-slider>
                <div class="slider-track">
                    <?php foreach ($media as $m):
                        $src = '../uploads/' . escape($m['file_name']);
                        if ($m['media_type'] === 'image'): ?>
                            <div class="slide"><img src="<?= $src ?>" alt="Imagem do serviço"></div>
                        <?php else: ?>
                            <div class="slide"><video src="<?= $src ?>" controls></video></div>
                        <?php endif;
                    endforeach; ?>
                </div>
                <button class="slider-nav prev" data-prev>&larr;</button>
                <button class="slider-nav next" data-next>&rarr;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($_SESSION['user_id'] != $service['user_id']): ?>
        <div class="actions">
        <form action="../actions/hire_service_action.php" method="post" class="inline-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="service_id" value="<?= escape($service_id) ?>">
            <button type="submit" class="button">Contratar</button>
        </form>
        <a href="messages_chat.php?user=<?= escape($service['user_id']) ?>" class="button">Enviar Mensagem</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
