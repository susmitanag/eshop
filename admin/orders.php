<?php
require __DIR__ . '/../config.php';
require_admin();

$pageTitle = 'Orders';
$adminPage = 'orders';
$statuses = ['Processing', 'Shipped', 'Delivered', 'Cancelled'];
$filter = $_GET['status'] ?? '';
if (!in_array($filter, $statuses, true)) { $filter = ''; }

$sql = 'SELECT o.*, u.email AS account_email FROM orders o JOIN users u ON u.id = o.user_id';
$params = [];
if ($filter !== '') { $sql .= ' WHERE o.status = ?'; $params[] = $filter; }
$sql .= ' ORDER BY o.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page-header"><h1>Orders</h1></div>

<div class="filter-pills">
    <a href="orders.php" class="<?= $filter === '' ? 'is-active' : '' ?>">All</a>
    <?php foreach ($statuses as $s): ?>
        <a href="orders.php?status=<?= e($s) ?>" class="<?= $filter === $s ? 'is-active' : '' ?>"><?= e($s) ?></a>
    <?php endforeach; ?>
</div>

<?php if (!$orders): ?>
    <div class="empty-state"><p>No orders here yet.</p></div>
<?php else: ?>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><strong>#<?= (int) $o['id'] ?></strong></td>
                <td><?= e($o['customer_name']) ?><br><span class="muted small"><?= e($o['account_email']) ?></span></td>
                <td><?= e(date('j M Y, g:ia', strtotime($o['created_at']))) ?></td>
                <td><?= money((float) $o['total']) ?></td>
                <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
                <td><a href="order_detail.php?id=<?= (int) $o['id'] ?>">Open</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
