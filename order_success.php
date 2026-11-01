<?php
require __DIR__ . '/config.php';
require_login();

$user = current_user();
$orderId = (int) ($_GET['order_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();
if (!$order) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT oi.product_id, oi.quantity, oi.unit_price, p.name, p.art, p.swatch, p.image, p.id FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$pageTitle = 'Order placed';
$currentPage = 'orders';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap narrow" style="padding-top:48px;padding-bottom:72px">
    <div class="success-head">
        <div class="success-icon"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg></div>
        <h1>Thank you, <?= e(explode(' ', $order['customer_name'])[0]) ?></h1>
        <p class="muted">Order <strong>#<?= (int) $order['id'] ?></strong> is confirmed. Pay <strong><?= money((float) $order['total']) ?></strong> when it arrives.</p>
    </div>
    <?php require __DIR__ . '/includes/order_view.php'; ?>
    <div class="center-actions">
        <a href="my_orders.php" class="btn btn-light">View my orders</a>
        <a href="shop.php" class="btn btn-primary">Keep shopping</a>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
