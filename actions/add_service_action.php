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

// Sanitizar inputs
$title         = sanitize($_POST['title'] ?? '');
$description   = sanitize($_POST['description'] ?? '');
$price         = sanitize($_POST['price'] ?? '0');
$delivery_time = sanitize($_POST['delivery_time'] ?? '1');
$category_id   = sanitize($_POST['category_id'] ?? '');

if (empty($title) || empty($description) || empty($price) || empty($delivery_time) || empty($category_id)) {
    flash('Campos obrigatórios em falta.', 'erro');
    header('Location: ../pages/add_service.php');
    exit();
}

try {
    $db = getConnection();
    // 1) Inserir serviço (imagem inicial null)
    $stmt = $db->prepare(
        "INSERT INTO services (user_id, category_id, title, description, price, delivery_time, image)
         VALUES (:uid, :cid, :t, :d, :p, :days, NULL)"
    );
    $stmt->execute([
        ':uid'   => $_SESSION['user_id'],
        ':cid'   => $category_id,
        ':t'     => $title,
        ':d'     => $description,
        ':p'     => $price,
        ':days'  => $delivery_time
    ]);
    $serviceId = $db->lastInsertId();

    // 2) Processar uploads múltiplos
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

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

    // 3) Atualizar a miniatura na tabela services
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
