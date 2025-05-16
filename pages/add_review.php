<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../database/connection.php';

require_login();

// Sanitizar e validar ID do pedido
$order_id = sanitize($_GET['order_id'] ?? '');
if (empty($order_id) || !is_numeric($order_id)) {
    flash('ID do pedido inválido.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}

// Carregar pedido
$db = getConnection();
$stmt = $db->prepare(
    "SELECT order_id, client_id, status FROM orders WHERE order_id = :oid"
);
$stmt->execute([':oid' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    flash('Pedido não encontrado.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}
if ($order['client_id'] != $_SESSION['user_id']) {
    flash('Não tens permissão para avaliar este pedido.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}
if ($order['status'] !== 'closed') {
    flash('Só é possível avaliar pedidos acabados.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}

// Verificar se já existe review
$stmtRev = $db->prepare(
    "SELECT review_id FROM reviews WHERE order_id = :oid"
);
$stmtRev->execute([':oid' => $order_id]);
if ($stmtRev->fetch(PDO::FETCH_ASSOC)) {
    flash('Este pedido já foi avaliado.', 'erro');
    header('Location: ../pages/my_orders.php');
    exit();
}

// Header e flash messages
require_once __DIR__ . '/../templates/header.php';
?>
<div class="review-container">
    <h2 class="review-title">Avaliar Pedido #<?= escape($order_id) ?></h2>

    <form action="../actions/review_action.php" method="post" class="review-form">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="order_id" value="<?= escape($order_id) ?>">

        <div class="form-group">
            <label for="rating">Nota (1 a 5):</label>
            <select name="rating" id="rating" required>
                <option value="">-- Selecione --</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>">
                    <?= $i ?>
                    <?= $i === 1 ? ' - Péssimo'
                        : ($i === 2 ? ' - Mau'
                        : ($i === 3 ? ' - Decente'
                        : ($i === 4 ? ' - Bom'
                        : ' - Excelente'))) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="comment">Comentário (opcional):</label>
            <textarea id="comment" name="comment" rows="4"><?= escape($comment ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-primary">Enviar Avaliação</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
