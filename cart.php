<?php
require __DIR__ . '/config.php';

$pageTitle = 'Your cart';
$currentPage = 'cart';

$isAjax = (
    ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
    || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $lineTotal = 0.0;

    if ($_POST['action'] === 'remove') {
        unset($_SESSION['cart'][$productId]);
    } elseif ($_POST['action'] === 'update') {
        $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $stock = (int) $stmt->fetchColumn();
        $qty = min(max(1, (int) ($_POST['qty'] ?? 1)), max(1, $stock));
        if ($stock > 0 && isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = $qty;
        }
    }

    if ($isAjax) {
        $lines = cart_lines($pdo);
        $sum = cart_summary($lines);
        $mine = null;
        foreach ($lines as $l) { if ((int) $l['product']['id'] === $productId) { $mine = $l; } }
        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'qty'           => $mine ? $mine['qty'] : 0,
            'line_total'    => $mine ? money($mine['line_total']) : '',
            'subtotal'      => money($sum['subtotal']),
            'shipping'      => $sum['shipping'] > 0 ? money($sum['shipping']) : 'Free',
            'total'         => money($sum['total']),
            'cart_count'    => $sum['count'],
            'to_free'       => money($sum['to_free']),
            'to_free_raw'   => $sum['to_free'],
            'progress'      => min(100, (int) round($sum['subtotal'] / FREE_SHIPPING_FROM * 100)),
            'is_empty'      => empty($lines),
        ]);
        exit;
    }
    header('Location: cart.php');
    exit;
}

$lines = cart_lines($pdo);
$sum = cart_summary($lines);
$progress = min(100, (int) round($sum['subtotal'] / FREE_SHIPPING_FROM * 100));

require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="wrap"><h1>Your cart</h1></div>
</section>

<div class="wrap cart-page" id="cart-content">
<?php if (!$lines): ?>
    <div class="empty-state" id="cart-empty-message">
        <h3>Your cart is empty</h3>
        <p>Add something you like and it will show up here.</p>
        <a href="shop.php" class="btn btn-primary">Start shopping</a>
    </div>
<?php else: ?>
    <div class="cart-layout">
        <div>
            <div class="ship-meter">
                <p id="js-ship-note"><?= $sum['to_free'] > 0 ? 'Add <strong>' . money($sum['to_free']) . '</strong> more for free delivery' : 'You get <strong>free delivery</strong> on this order' ?></p>
                <div class="meter"><span id="js-meter" style="width:<?= $progress ?>%"></span></div>
            </div>
            <div class="cart-items" id="cart-rows">
                <?php foreach ($lines as $line): $p = $line['product']; ?>
                    <div class="cart-row" data-product-id="<?= (int) $p['id'] ?>" data-price="<?= e($p['price']) ?>">
                        <a class="cart-thumb" href="product.php?id=<?= (int) $p['id'] ?>"><?= product_image($p) ?></a>
                        <div class="cart-info">
                            <a class="cart-name" href="product.php?id=<?= (int) $p['id'] ?>"><?= e($p['name']) ?></a>
                            <span class="muted"><?= money((float) $p['price']) ?> each</span>
                            <form method="post" action="cart.php" class="js-remove-form">
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="link-btn">Remove</button>
                            </form>
                        </div>
                        <form method="post" action="cart.php" class="js-update-form">
                            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                            <input type="hidden" name="action" value="update">
                            <div class="qty-stepper qty-small">
                                <button type="button" class="qty-btn" data-step="-1" aria-label="Decrease quantity">&minus;</button>
                                <input type="number" name="qty" value="<?= (int) $line['qty'] ?>" min="1" max="<?= (int) $p['stock'] ?>" class="js-qty-input" aria-label="Quantity">
                                <button type="button" class="qty-btn" data-step="1" aria-label="Increase quantity">&plus;</button>
                            </div>
                            <noscript><button type="submit" class="btn btn-small">Update</button></noscript>
                        </form>
                        <div class="cart-line js-line-total"><?= money($line['line_total']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="summary-card">
            <h2>Order summary</h2>
            <dl class="summary-list">
                <div><dt>Subtotal</dt><dd id="js-subtotal"><?= money($sum['subtotal']) ?></dd></div>
                <div><dt>Delivery</dt><dd id="js-shipping"><?= $sum['shipping'] > 0 ? money($sum['shipping']) : 'Free' ?></dd></div>
                <div class="summary-total"><dt>Total</dt><dd id="js-total"><?= money($sum['total']) ?></dd></div>
            </dl>
            <a href="checkout.php" class="btn btn-primary btn-lg btn-block">Checkout</a>
            <p class="muted small">You pay when the order arrives.</p>
        </aside>
    </div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
