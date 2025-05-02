<?php
// actions/edit_service_action.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$service_id    = $_POST['service_id'] ?? null;
$title         = trim($_POST['title'] ?? '');
$description   = trim($_POST['description'] ?? '');
$price         = $_POST['price'] ?? '';
$delivery_time = $_POST['delivery_time'] ?? '';
$category_id   = $_POST['category_id'] ?? '';

if (!$service_id || !is_numeric($service_id)) {
    die("ID de serviço inválido.");
}

// Verifica se o serviço pertence ao user logado
$stmt = $db->prepare("SELECT user_id FROM services WHERE service_id = :id");
$stmt->execute([':id' => $service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Serviço não encontrado.");
}
if ($service['user_id'] != $_SESSION['user_id']) {
    die("Não tens permissão para editar este serviço.");
}

// Validações simples
if (empty($title) || empty($description) || empty($price) || empty($delivery_time) || empty($category_id)) {
    die("Preencha todos os campos necessários.");
}

// Converter para float/int se necessário
$price = floatval($price);
$delivery_time = intval($delivery_time);

// Atualiza o serviço
try {
    $stmt = $db->prepare("
        UPDATE services
        SET 
            title = :title,
            description = :description,
            price = :price,
            delivery_time = :delivery_time,
            category_id = :category_id
        WHERE service_id = :service_id
    ");
    $stmt->execute([
        ':title'         => $title,
        ':description'   => $description,
        ':price'         => $price,
        ':delivery_time' => $delivery_time,
        ':category_id'   => $category_id,
        ':service_id'    => $service_id
    ]);

    header('Location: ../pages/my_services.php');
    exit();
} catch (PDOException $e) {
    die("Erro ao atualizar serviço: " . $e->getMessage());
}
