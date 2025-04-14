<?php
// actions/delete_service_action.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

$service_id = $_POST['service_id'] ?? null;
if (!$service_id || !is_numeric($service_id)) {
    die("ID de serviço inválido.");
}

// Verifica se o serviço é do user logado
$stmt = $db->prepare("SELECT user_id FROM services WHERE service_id = :id");
$stmt->execute([':id' => $service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Serviço não encontrado.");
}

if ($service['user_id'] != $_SESSION['user_id']) {
    die("Não tens permissão para excluir este serviço.");
}

// Se chegou aqui, pode excluir
try {
    $stmt = $db->prepare("DELETE FROM services WHERE service_id = :id");
    $stmt->execute([':id' => $service_id]);

    // Redireciona de volta para a lista de serviços
    header('Location: ../pages/my_services.php');
    exit();
} catch (PDOException $e) {
    die("Erro ao excluir serviço: " . $e->getMessage());
}
