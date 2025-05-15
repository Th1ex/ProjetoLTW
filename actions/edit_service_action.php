<?php
require_once '../includes/security.php';
require_once '../includes/auth.php';
require_once '../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/my_services.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

// Sanitizar inputs
$service_id    = sanitize($_POST['service_id'] ?? '');
$title         = sanitize($_POST['title'] ?? '');
$description   = sanitize($_POST['description'] ?? '');
$price         = sanitize($_POST['price'] ?? '0');
$delivery_time = sanitize($_POST['delivery_time'] ?? '0');
$category_id   = sanitize($_POST['category_id'] ?? '');

// Validações
if (empty($service_id) || !is_numeric($service_id)) {
    flash('ID de serviço inválido.', 'erro');
    header('Location: ../pages/my_services.php');
    exit();
}

if (empty($title) || empty($description) || empty($price) || empty($delivery_time) || empty($category_id)) {
    flash('Preencha todos os campos necessários.', 'erro');
    header('Location: ../pages/my_services.php');
    exit();
}

try {
    $db = getConnection();

    // Verifica propriedade
    $stmt = $db->prepare(
        "SELECT user_id FROM services WHERE service_id = :id"
    );
    $stmt->execute([':id' => $service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        flash('Serviço não encontrado.', 'erro');
        header('Location: ../pages/my_services.php');
        exit();
    }

    if ($service['user_id'] != $_SESSION['user_id']) {
        flash('Não tens permissão para editar este serviço.', 'erro');
        header('Location: ../pages/my_services.php');
        exit();
    }

    // Converter tipos
    $price = floatval($price);
    $delivery_time = intval($delivery_time);

    // Atualizar serviço
    $update = $db->prepare(
        "UPDATE services SET title = :title, description = :description, price = :price, delivery_time = :delivery_time, category_id = :category_id WHERE service_id = :service_id"
    );
    $update->execute([
        ':title'         => $title,
        ':description'   => $description,
        ':price'         => $price,
        ':delivery_time' => $delivery_time,
        ':category_id'   => $category_id,
        ':service_id'    => $service_id
    ]);

    flash('Serviço atualizado com sucesso!', 'sucesso');
    header('Location: ../pages/my_services.php');
    exit();
} catch (PDOException $e) {
    flash('Erro ao atualizar serviço: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/my_services.php');
    exit();
}
?>
