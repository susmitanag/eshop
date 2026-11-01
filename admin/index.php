<?php
require __DIR__ . '/../config.php';
require_admin();

$pageTitle = 'Dashboard';
$adminPage = 'dashboard';

$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$orderCount = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pending = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Processing'")->fetchColumn();
$revenue = (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'Cancelled'")->fetchColumn();
$lowStock = $pdo->query('SELECT * FROM products WHERE stock < 15 ORDER BY stock ASC LIMIT 8')->fetchAll();
$recent = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 6')->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Dashboard</h1>

<div class="stat-grid">
    <div class="stat-card"><span class="stat-value"><?= money($revenue) ?></span><span class="stat-label">Revenue</span></div>
    <div class="stat-card"><span class="stat-value"><?= $orderCount ?></span><span class="stat-label">Orders</span></div>
    <div class="stat-card"><span class="stat-value"><?= $pending ?></span><span class="stat-label">Waiting to ship</span></div>
    <div class="stat-card"><span class="stat-value"><?= $productCount ?></span><span class="stat-label">Products</span></div>
</div>

<div class="admin-two">
    <div>
        <h2>Recent orders</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $o): ?>
                    <tr>
                        <td><a href="order_detail.php?id=<?= (int) $o['id'] ?>">#<?= (int) $o['id'] ?></a></td>
                        <td><?= e($o['customer_name']) ?></td>
                        <td><?= money((float) $o['total']) ?></td>
                        <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recent): ?><tr><td colspan="4" class="muted">No orders yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <h2>Low stock</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Product</th><th>Left</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($lowStock as $p): ?>
                    <tr>
                        <td><div class="cell-product"><span class="admin-thumb"><?= product_image($p, '../') ?></span><?= e($p['name']) ?></div></td>
                        <td><strong><?= (int) $p['stock'] ?></strong></td>
                        <td><a href="product_form.php?id=<?= (int) $p['id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$lowStock): ?><tr><td colspan="3" class="muted">Everything is well stocked.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
