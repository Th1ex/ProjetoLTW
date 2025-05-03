<?php
// pages/add_review.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../database/connection.php';

$db = getConnection();

$order_id = $_GET['order_id'] ?? null;
if (!$order_id || !is_numeric($order_id)) {
    die("ID do pedido inválido.");
}

// Verifica se este pedido pertence ao user logado
$stmt = $db->prepare("
    SELECT order_id, client_id, status 
    FROM orders
    WHERE order_id = :oid
");
$stmt->execute([':oid' => $order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Pedido não encontrado.");
}
if ($order['client_id'] != $_SESSION['user_id']) {
    die("Não tens permissão para avaliar este pedido.");
}
if ($order['status'] !== 'closed') {
    die("Só é possível avaliar pedidos acabados.");
}

// Verifica se já existe uma review para este pedido
$stmtRev = $db->prepare("SELECT review_id FROM reviews WHERE order_id = :oid");
$stmtRev->execute([':oid' => $order_id]);
if ($stmtRev->fetch()) {
    die("Este pedido já foi avaliado.");
}
?>

<h2>Avaliar Pedido #<?= htmlspecialchars($order_id) ?></h2>

<form action="../actions/review_action.php" method="post">
    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">
    
    <label for="rating">Nota (1 a 5):</label>
    <select name="rating" id="rating" required>
        <option value="">-- Selecione --</option>
        <option value="1">1 - Péssimo</option>
        <option value="2">2 - Mau</option>
        <option value="3">3 - Decente</option>
        <option value="4">4 - Bom</option>
        <option value="5">5 - Excelente</option>
    </select>

    <label for="comment">Comentário (opcional):</label>
    <textarea id="comment" name="comment" rows="4"></textarea>

    <button type="submit">Enviar Avaliação</button>
</form>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
