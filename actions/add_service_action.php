<?php
// actions/add_service_action.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

// Coleta de dados do formulário
$title         = trim($_POST['title'] ?? '');
$description   = trim($_POST['description'] ?? '');
$price         = $_POST['price'] ?? '';
$delivery_time = $_POST['delivery_time'] ?? '';
$category_id   = $_POST['category_id'] ?? '';
$user_id       = $_SESSION['user_id'];

if (empty($title) || empty($description) || empty($price) || empty($delivery_time) || empty($category_id)) {
    die("Preencha todos os campos necessários.");
}

$price = floatval($price);
$delivery_time = intval($delivery_time);

// Processar o upload da imagem (opcional)
$imagePath = null;  // Valor padrão se nenhuma imagem for enviada.

if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {

    // Pasta de destino para os uploads (certifique-se de que a pasta 'uploads' existe ou será criada)
    $uploadDir = __DIR__ . '/../uploads/';
    
    // Cria o diretório se não existir
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Obtém dados do arquivo
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Verifica se a extensão é permitida
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExt, $allowedExts)) {
        die("Formato de imagem não permitido. Escolha JPG, JPEG, PNG ou GIF.");
    }
    
    // Gera um nome único para o arquivo
    $newFileName = uniqid('img_', true) . '.' . $fileExt;
    $destPath = $uploadDir . $newFileName;
    
    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        die("Erro ao mover o arquivo de imagem.");
    }
    
    // Armazena o nome do arquivo (pode ser o caminho relativo) no banco de dados
    $imagePath = $newFileName;
}

try {
    // Insere o serviço, incluindo o campo 'image'
    $stmt = $db->prepare("
        INSERT INTO services 
            (user_id, category_id, title, description, price, delivery_time, image) 
        VALUES 
            (:user_id, :category_id, :title, :description, :price, :delivery_time, :image)
    ");
    $stmt->execute([
        ':user_id'       => $user_id,
        ':category_id'   => $category_id,
        ':title'         => $title,
        ':description'   => $description,
        ':price'         => $price,
        ':delivery_time' => $delivery_time,
        ':image'         => $imagePath  // Pode ser null se nenhum arquivo foi enviado
    ]);

    header('Location: ../pages/list_services.php');
    exit();

} catch (PDOException $e) {
    die("Erro ao inserir serviço: " . $e->getMessage());
}
