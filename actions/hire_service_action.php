<?php
// actions/hire_service_action.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

// Lê o service_id do POST
$service_id = $_POST['service_id'] ?? null;
if (!$service_id || !is_numeric($service_id)) {
    die("ID de serviço inválido.");
}

// Consulta o serviço para verificar se existe, e obter o preço
$stmt = $db->prepare("SELECT price, user_id FROM services WHERE service_id = :id");
$stmt->execute([':id' => $service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Serviço não encontrado.");
}

// Impedir que o dono do serviço contrate o próprio serviço (opcional)
if ($service['user_id'] == $_SESSION['user_id']) {
    die("Não é possível contratar o próprio serviço.");
}

// Cria o pedido (order)
$client_id = $_SESSION['user_id'];
$status = 'pending';
$total_price = $service['price']; // Copia o preço atual do serviço

try {
    $stmt = $db->prepare("
        INSERT INTO orders (service_id, client_id, total_price, status)
        VALUES (:service_id, :client_id, :total_price, :status)
    ");
    $stmt->execute([
        ':service_id'   => $service_id,
        ':client_id'    => $client_id,
        ':total_price'  => $total_price,
        ':status'       => $status
    ]);

    // Redireciona para onde preferires; aqui usamos a home
    header('Location: ../pages/home.php');
    exit();
} catch (PDOException $e) {
    die("Erro ao criar pedido: " . $e->getMessage());
}
