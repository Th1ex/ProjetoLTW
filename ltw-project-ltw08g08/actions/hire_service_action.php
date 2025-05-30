<?php
require_once '../includes/security.php';
require_once '../includes/auth.php';
require_once '../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método inválido.', 'erro');
    header('Location: ../pages/list_services.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

// Sanitizar input
$service_id = sanitize($_POST['service_id'] ?? '');
if (empty($service_id) || !is_numeric($service_id)) {
    flash('ID de serviço inválido.', 'erro');
    header('Location: ../pages/list_services.php');
    exit();
}

try {
    $db = getConnection();

    // Consulta o serviço para verificar existência e obter dados
    $stmt = $db->prepare("SELECT price, user_id FROM services WHERE service_id = :id");
    $stmt->execute([':id' => $service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        flash('Serviço não encontrado.', 'erro');
        header('Location: ../pages/list_services.php');
        exit();
    }

    // Impedir contratação do próprio serviço
    if ($service['user_id'] == $_SESSION['user_id']) {
        flash('Não é possível contratar o próprio serviço.', 'erro');
        header('Location: ../pages/list_services.php');
        exit();
    }

    // Cria o pedido
    $stmt = $db->prepare(
        "INSERT INTO orders (service_id, client_id, total_price, status) VALUES (:service_id, :client_id, :total_price, :status)"
    );
    $stmt->execute([
        ':service_id'  => $service_id,
        ':client_id'   => $_SESSION['user_id'],
        ':total_price' => $service['price'],
        ':status'      => 'pending'
    ]);

    flash('Pedido criado com sucesso!', 'sucesso');
    header('Location: ../pages/list_services.php');
    exit();

} catch (PDOException $e) {
    flash('Erro ao criar pedido: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/list_services.php');
    exit();
}
?>