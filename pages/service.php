<?php
// projeto_ltw/pages/service.php

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$service_id = $_GET['id'] ?? null;
if (!$service_id || !is_numeric($service_id)) {
    echo "<p>ID de serviço inválido.</p>";
    require_once __DIR__ . '/../templates/footer.php';
    exit();
}

$db = getConnection();

$stmt = $db->prepare("
    SELECT 
        s.service_id,
        s.title,
        s.description,
        s.price,
        s.delivery_time,
        s.image,
        s.user_id AS serviceUserId,
        c.category_name,
        u.username AS freelancer
    FROM services s
    JOIN categories c ON s.category_id = c.category_id
    JOIN users u ON s.user_id = u.user_id
    WHERE s.service_id = :id
");
$stmt->bindValue(':id', $service_id, PDO::PARAM_INT);
$stmt->execute();
$service = $stmt->fetch();

if (!$service) {
    echo "<p>Serviço não encontrado.</p>";
    require_once __DIR__ . '/../templates/footer.php';
    exit();
}

$serviceUserId = $service['serviceUserId'];
?>

<h2><?= htmlspecialchars($service['title']) ?></h2>
<p><strong>Freelancer:</strong> <?= htmlspecialchars($service['freelancer']) ?></p>
<p><strong>Categoria:</strong> <?= htmlspecialchars($service['category_name']) ?></p>
<p><strong>Preço:</strong> <?= htmlspecialchars($service['price']) ?> €</p>
<p><strong>Tempo de entrega:</strong> <?= htmlspecialchars($service['delivery_time']) ?> dias</p>

<?php if (!empty($service['image'])): ?>
    <img src="../uploads/<?= htmlspecialchars($service['image']) ?>" alt="Imagem do Serviço" style="max-width:250px; height:auto;">
<?php else: ?>
    <p>Este serviço não tem imagem.</p>
<?php endif; ?>

<h3>Descrição</h3>
<p><?= nl2br(htmlspecialchars($service['description'])) ?></p>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
