<?php
// actions/add_service_action.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}
require_once __DIR__ . '/../database/connection.php';
$db = getConnection();

/* ── dados do formulário ───────────────────────────────────────── */
$title   = trim($_POST['title'] ?? '');
$descr   = trim($_POST['description'] ?? '');
$price   = $_POST['price'] ?? 0;
$days    = $_POST['delivery_time'] ?? 1;
$catId   = $_POST['category_id'] ?? null;

if ($title==='' || $descr==='' || !$catId) {
    die('Campos obrigatórios em falta.');
}

/* ── 1) cria o serviço ─────────────────────────────────────────── */
$stmt = $db->prepare("
  INSERT INTO services
        (user_id, category_id, title, description, price, delivery_time, image)
  VALUES (:uid, :cid, :t, :d, :p, :days, NULL)
");
$stmt->execute([
  ':uid'  => $_SESSION['user_id'],
  ':cid'  => $catId,
  ':t'    => $title,
  ':d'    => $descr,
  ':p'    => $price,
  ':days' => $days
]);

$serviceId = $db->lastInsertId();   // ← guardamos aqui uma única vez
$imageMini = null;                  // primeira imagem (miniatura)

/* ── 2) processa uploads múltiplos ─────────────────────────────── */
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755);

foreach ($_FILES['files']['name'] as $i => $origName) {
    $tmp = $_FILES['files']['tmp_name'][$i];
    if (!is_uploaded_file($tmp)) continue;               // ignora vazios

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $new = uniqid('media_') . '.' . $ext;
    move_uploaded_file($tmp, $uploadDir . $new);

    /* tipo: image ou video */
    $mime = $_FILES['files']['type'][$i] ?? '';
    $type = str_starts_with($mime, 'video') ? 'video' : 'image';

    /* grava em service_media */
    $db->prepare("
        INSERT INTO service_media (service_id, file_name, media_type)
        VALUES (:sid, :fn, :mt)
    ")->execute([
        ':sid' => $serviceId,   // ← usa sempre o mesmo service_id
        ':fn'  => $new,
        ':mt'  => $type
    ]);

    /* define miniatura se ainda não definida e ficheiro é imagem */
    if ($type === 'image' && $imageMini === null) {
        $imageMini = $new;
    }
}

/* ── 3) guarda a miniatura na coluna image (compatibilidade) ───── */
if ($imageMini !== null) {
    $db->prepare("
        UPDATE services SET image = :img WHERE service_id = :sid
    ")->execute([
        ':img' => $imageMini,
        ':sid' => $serviceId
    ]);
}

/* ── 4) redirecciona ───────────────────────────────────────────── */
header('Location: ../pages/my_services.php');
exit();
