<?php
require __DIR__ . '/config.php';
require_login();

$user = current_user();
$orderId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();
if (!$order) { header('Location: my_orders.php'); exit; }

$stmt = $pdo->prepare('SELECT oi.product_id, oi.quantity, oi.unit_price, p.name, p.art, p.swatch, p.image, p.id FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$pageTitle = 'Order #' . $order['id'];
$currentPage = 'orders';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap narrow" style="padding-top:36px;padding-bottom:72px">
    <p class="breadcrumb"><a href="my_orders.php">My orders</a> / Order #<?= (int) $order['id'] ?></p>
    <h1>Order #<?= (int) $order['id'] ?></h1>
    <p class="muted">Placed on <?= e(date('j M Y, g:ia', strtotime($order['created_at']))) ?></p>
    <?php require __DIR__ . '/includes/order_view.php'; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
