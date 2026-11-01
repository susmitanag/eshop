<?php
require __DIR__ . '/../config.php';
require_admin();

$adminPage = 'orders';
$statuses = ['Processing', 'Shipped', 'Delivered', 'Cancelled'];
$orderId = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, $statuses, true)) {
        $pdo->beginTransaction();
        try {
            $cur = $pdo->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
            $cur->execute([$orderId]);
            $old = $cur->fetchColumn();
            if ($old !== false && $old !== $newStatus) {
                // Cancelling an order puts the items back in stock. Un-cancelling takes them out again.
                if ($newStatus === 'Cancelled' && $old !== 'Cancelled') {
                    $pdo->prepare('UPDATE products p JOIN order_items oi ON oi.product_id = p.id SET p.stock = p.stock + oi.quantity WHERE oi.order_id = ?')->execute([$orderId]);
                } elseif ($old === 'Cancelled' && $newStatus !== 'Cancelled') {
                    $pdo->prepare('UPDATE products p JOIN order_items oi ON oi.product_id = p.id SET p.stock = GREATEST(0, p.stock - oi.quantity) WHERE oi.order_id = ?')->execute([$orderId]);
                }
                $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$newStatus, $orderId]);
            }
            $pdo->commit();
            $_SESSION['admin_flash'] = 'Order status updated.';
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $_SESSION['admin_flash'] = 'Could not update the order.';
        }
    }
    header('Location: order_detail.php?id=' . $orderId);
    exit;
}

$stmt = $pdo->prepare('SELECT o.*, u.email AS account_email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();
if (!$order) { header('Location: orders.php'); exit; }

$stmt = $pdo->prepare('SELECT oi.product_id, oi.quantity, oi.unit_price, p.name, p.art, p.swatch, p.image, p.id FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$flash = $_SESSION['admin_flash'] ?? '';
unset($_SESSION['admin_flash']);
$admin = true;
$pageTitle = 'Order #' . $order['id'];
require __DIR__ . '/../includes/admin_header.php';
?>
<p class="breadcrumb"><a href="orders.php">Orders</a> / #<?= (int) $order['id'] ?></p>
<div class="admin-page-header">
    <div>
        <h1 style="margin-bottom:4px">Order #<?= (int) $order['id'] ?></h1>
        <span class="muted">Placed <?= e(date('j M Y, g:ia', strtotime($order['created_at']))) ?> by <?= e($order['account_email']) ?></span>
    </div>
    <form method="post" class="status-form">
        <label for="status" class="muted">Status</label>
        <select id="status" name="status">
            <?php foreach ($statuses as $s): ?><option <?= $order['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-primary">Update</button>
    </form>
</div>
<?php if ($flash): ?><div class="alert alert-ok"><?= e($flash) ?></div><?php endif; ?>
<div style="max-width:780px"><?php require __DIR__ . '/../includes/order_view.php'; ?></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
