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

$service_id = sanitize($_POST['service_id'] ?? '');
if (empty($service_id) || !is_numeric($service_id)) {
    flash('ID de serviço inválido.', 'erro');
    header('Location: ../pages/my_services.php');
    exit();
}

try {
    $db = getConnection();

    // Verifica se o serviço pertence ao usuário logado
    $stmt = $db->prepare("SELECT user_id FROM services WHERE service_id = :id");
    $stmt->execute([':id' => $service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        flash('Serviço não encontrado.', 'erro');
        header('Location: ../pages/my_services.php');
        exit();
    }

    if ($service['user_id'] != $_SESSION['user_id']) {
        flash('Não tens permissão para excluir este serviço.', 'erro');
        header('Location: ../pages/my_services.php');
        exit();
    }

    // Excluir o serviço
    $delStmt = $db->prepare("DELETE FROM services WHERE service_id = :id");
    $delStmt->execute([':id' => $service_id]);

    flash('Serviço excluído com sucesso!', 'sucesso');
} catch (PDOException $e) {
    flash('Erro ao excluir serviço: ' . $e->getMessage(), 'erro');
}

header('Location: ../pages/my_services.php');
exit();
