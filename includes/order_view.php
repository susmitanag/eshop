<?php
// Shared order summary used by order_success.php and order_detail.php.
// Expects $order, $items. Optional $admin (bool).
$steps = ['Processing', 'Shipped', 'Delivered'];
$cancelled = $order['status'] === 'Cancelled';
$stepIndex = array_search($order['status'], $steps, true);
?>
<?php if ($cancelled): ?>
    <p class="alert alert-error">This order was cancelled.</p>
<?php else: ?>
    <ol class="timeline">
        <?php foreach ($steps as $i => $st): ?>
            <li class="<?= $i < $stepIndex ? 'is-done' : ($i === $stepIndex ? 'is-now' : '') ?>"><span class="tl-dot"></span><?= e($st) ?></li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<div class="panel">
    <ul class="order-lines">
        <?php foreach ($items as $it): ?>
            <li>
                <span class="mini-thumb"><?= product_image($it, !empty($admin) ? '../' : '') ?><em><?= (int) $it['quantity'] ?></em></span>
                <a class="mini-name" href="<?= !empty($admin) ? '../' : '' ?>product.php?id=<?= (int) $it['product_id'] ?>"><?= e($it['name']) ?></a>
                <span><?= money((float) $it['unit_price'] * (int) $it['quantity']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <dl class="summary-list">
        <div><dt>Subtotal</dt><dd><?= money((float) $order['subtotal']) ?></dd></div>
        <div><dt>Delivery</dt><dd><?= (float) $order['shipping'] > 0 ? money((float) $order['shipping']) : 'Free' ?></dd></div>
        <div class="summary-total"><dt>Total</dt><dd><?= money((float) $order['total']) ?></dd></div>
    </dl>
</div>
<div class="panel">
    <h3>Delivery</h3>
    <p style="margin:0"><?= e($order['customer_name']) ?><br><?= nl2br(e($order['customer_address'])) ?><br><span class="muted"><?= e($order['customer_phone']) ?> / <?= e($order['customer_email']) ?></span></p>
</div>
