<?php
// actions/add_service_action.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

// Coletando dados do formulário
$title         = trim($_POST['title'] ?? '');
$description   = trim($_POST['description'] ?? '');
$price         = $_POST['price'] ?? '';
$delivery_time = $_POST['delivery_time'] ?? '';
$category_id   = $_POST['category_id'] ?? '';
$user_id       = $_SESSION['user_id']; // freelancer que está a criar o serviço

// Validação simples
if (empty($title) || empty($description) || empty($price) || empty($delivery_time) || empty($category_id)) {
    die("Preencha todos os campos necessários.");
}

// Transforma possíveis valores em números (ou floats) corretamente, se necessário
$price = floatval($price);
$delivery_time = intval($delivery_time);

try {
    // Prepara o INSERT
    $stmt = $db->prepare("
        INSERT INTO services 
            (user_id, category_id, title, description, price, delivery_time) 
        VALUES 
            (:user_id, :category_id, :title, :description, :price, :delivery_time)
    ");

    $stmt->execute([
        ':user_id'       => $user_id,
        ':category_id'   => $category_id,
        ':title'         => $title,
        ':description'   => $description,
        ':price'         => $price,
        ':delivery_time' => $delivery_time
    ]);

    // Podemos redirecionar para a listagem ou página do serviço criado
    header('Location: ../pages/list_services.php');
    exit();

} catch (PDOException $e) {
    die("Erro ao inserir serviço: " . $e->getMessage());
}
