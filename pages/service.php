<?php
// pages/service.php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

// Lê o id do serviço da query string
$service_id = $_GET['id'] ?? null;
if (!$service_id || !is_numeric($service_id)) {
    echo "<p>ID de serviço inválido.</p>";
    require_once __DIR__ . '/../templates/footer.php';
    exit();
}

$db = getConnection();

// Busca o serviço pelo ID e inclui o user_id para sabermos se o user logado é o dono
$stmt = $db->prepare("
    SELECT 
        services.service_id,
        services.title, 
        services.description, 
        services.price, 
        services.delivery_time,
        services.user_id AS serviceUserId,
        categories.category_name, 
        users.username AS freelancer
    FROM services
    JOIN categories ON services.category_id = categories.category_id
    JOIN users ON services.user_id = users.user_id
    WHERE services.service_id = :id
");
$stmt->bindValue(':id', $service_id, PDO::PARAM_INT);
$stmt->execute();
$service = $stmt->fetch();

if (!$service) {
    echo "<p>Serviço não encontrado.</p>";
    require_once __DIR__ . '/../templates/footer.php';
    exit();
}

// Guardamos o user_id do dono do serviço para poder verificar se o user logado é o mesmo
$serviceUserId = $service['serviceUserId'];
?>

<h2><?= htmlspecialchars($service['title']) ?></h2>

<p><strong>Freelancer:</strong> <?= htmlspecialchars($service['freelancer']) ?></p>
<p><strong>Categoria:</strong> <?= htmlspecialchars($service['category_name']) ?></p>
<p><strong>Preço:</strong> <?= htmlspecialchars($service['price']) ?> €</p>
<p><strong>Tempo de entrega:</strong> <?= htmlspecialchars($service['delivery_time']) ?> dias</p>

<h3>Descrição</h3>
<p><?= nl2br(htmlspecialchars($service['description'])) ?></p>

<?php 
// Se o usuário estiver logado e NÃO for o dono do serviço, mostra o botão "Contratar"
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_id'] != $serviceUserId) {
        ?>
        <form action="../actions/hire_service_action.php" method="post">
            <!-- O ID do serviço vai no input hidden -->
            <input type="hidden" name="service_id" value="<?= htmlspecialchars($service_id) ?>">
            <button type="submit">Contratar</button>
        </form>
        <?php
    }
}
?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
