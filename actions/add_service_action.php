<?php
// actions/add_service_action.php

require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

session_start();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Método de acesso inválido.', 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}

verify_csrf($_POST['csrf_token'] ?? '');

// Captura de inputs brutos
$titleRaw       = $_POST['title'] ?? '';
$descriptionRaw = $_POST['description'] ?? '';
$priceRaw       = $_POST['price'] ?? '';
$deliveryRaw    = $_POST['delivery_time'] ?? '';
$categoryRaw    = $_POST['category_id'] ?? '';

// Sanitização básica (remove tags e espaços extras)
$title       = sanitize($titleRaw);
$description = sanitize($descriptionRaw);

// Verificação de campos obrigatórios
if (trim($title) === '' || trim($description) === '' || $priceRaw === '' || $deliveryRaw === '' || $categoryRaw === '') {
    flash('Campos obrigatórios em falta.', 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}

// Validações sem regex
// 1) Título: 5 a 100 caracteres
$lenTitle = mb_strlen($title);
if ($lenTitle < 5 || $lenTitle > 100) {
    flash('O título deve ter entre 5 e 100 caracteres.', 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}

// 2) Descrição: 10 a 1000 caracteres
$lenDesc = mb_strlen($description);
if ($lenDesc < 10 || $lenDesc > 1000) {
    flash('A descrição deve ter entre 10 e 1000 caracteres.', 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}

// 3) Preço: numérico e >= 0, até duas casas decimais
if (!is_numeric($priceRaw) || $priceRaw < 0) {
    flash('O preço deve ser um número válido.', 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}
$parts = explode('.', $priceRaw);
if (isset($parts[1]) && strlen($parts[1]) > 2) {
    flash('O preço pode ter no máximo duas casas decimais.', 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}
$price = number_format((float)$priceRaw, 2, '.', '');

// 4) Tempo de entrega: inteiro de 1 a 999
if (!ctype_digit($deliveryRaw) || (int)$deliveryRaw < 1 || (int)$deliveryRaw > 999) {
    flash('Tempo de entrega inválido.', 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}
$deliveryInt = (int)$deliveryRaw;

// 5) Categoria: inteiro positivo
if (!ctype_digit($categoryRaw) || (int)$categoryRaw < 1) {
    flash('Categoria inválida.', 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}
$categoryInt = (int)$categoryRaw;

try {
    $db = getConnection();
    // Inserir serviço (imagem inicial null)
    $stmt = $db->prepare(
        "INSERT INTO services (user_id, category_id, title, description, price, delivery_time, image)
         VALUES (:uid, :cid, :t, :d, :p, :days, NULL)"
    );
    $stmt->execute([
        ':uid'   => $_SESSION['user_id'],
        ':cid'   => $categoryInt,
        ':t'     => $title,
        ':d'     => $description,
        ':p'     => $price,
        ':days'  => $deliveryInt
    ]);
    $serviceId = $db->lastInsertId();

    // Processar uploads múltiplos
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $imageMini = null;
    if (!empty($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
        foreach ($_FILES['files']['name'] as $i => $origName) {
            $tmp = $_FILES['files']['tmp_name'][$i] ?? null;
            if (!$tmp || !is_uploaded_file($tmp)) continue;

            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $newFilename = uniqid('media_') . '.' . $ext;
            $target = $uploadDir . $newFilename;

            if (move_uploaded_file($tmp, $target)) {
                $mime = $_FILES['files']['type'][$i] ?? '';
                $type = str_starts_with($mime, 'video') ? 'video' : 'image';

                $mStmt = $db->prepare(
                    "INSERT INTO service_media (service_id, file_name, media_type)
                     VALUES (:sid, :fn, :mt)"
                );
                $mStmt->execute([
                    ':sid' => $serviceId,
                    ':fn'  => $newFilename,
                    ':mt'  => $type
                ]);

                if ($type === 'image' && $imageMini === null) {
                    $imageMini = $newFilename;
                }
            }
        }
    }

    // Atualizar a miniatura na tabela services
    if ($imageMini !== null) {
        $uStmt = $db->prepare(
            "UPDATE services SET image = :img WHERE service_id = :sid"
        );
        $uStmt->execute([
            ':img' => $imageMini,
            ':sid' => $serviceId
        ]);
    }

    flash('Serviço adicionado com sucesso!', 'sucesso');
    header('Location: ../pages/my_services.php');
    exit();

} catch (PDOException $e) {
    flash('Erro ao adicionar serviço: ' . $e->getMessage(), 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}
?>
