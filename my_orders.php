<?php
require __DIR__ . '/config.php';
require_login();

$pageTitle = 'My orders';
$currentPage = 'orders';
$user = current_user();

$stmt = $pdo->prepare('SELECT o.*, (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS item_count FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="wrap"><h1>My orders</h1><p class="muted">Hi <?= e(explode(' ', $user['name'])[0]) ?>, here is everything you have ordered.</p></div>
</section>
<div class="wrap" style="padding-top:32px;padding-bottom:72px">
<?php if (!$orders): ?>
    <div class="empty-state"><h3>No orders yet</h3><p>When you place an order, you can follow it here.</p><a href="shop.php" class="btn btn-primary">Start shopping</a></div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><strong>#<?= (int) $o['id'] ?></strong></td>
                    <td><?= e(date('j M Y', strtotime($o['created_at']))) ?></td>
                    <td><?= (int) $o['item_count'] ?></td>
                    <td><?= money((float) $o['total']) ?></td>
                    <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
                    <td><a href="order_detail.php?id=<?= (int) $o['id'] ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
