<?php
require __DIR__ . '/config.php';
require_login();

$pageTitle = 'Checkout';
$currentPage = 'cart';
$user = current_user();

$lines = cart_lines($pdo);
if (empty($lines)) {
    header('Location: cart.php');
    exit;
}
$sum = cart_summary($lines);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (mb_strlen($name) < 2) $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) $errors[] = 'Please enter a phone number we can call.';
    if (mb_strlen($address) < 10) $errors[] = 'Please enter your full delivery address.';

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            // Lock the product rows so two shoppers cannot buy the last item at the same time.
            $ids = array_map(fn($l) => (int) $l['product']['id'], $lines);
            $marks = implode(',', array_fill(0, count($ids), '?'));
            $lock = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($marks) FOR UPDATE");
            $lock->execute($ids);
            $fresh = [];
            foreach ($lock->fetchAll() as $row) { $fresh[(int) $row['id']] = $row; }

            $subtotal = 0.0;
            foreach ($lines as $l) {
                $row = $fresh[(int) $l['product']['id']] ?? null;
                if (!$row || (int) $row['stock'] < $l['qty']) {
                    throw new RuntimeException('Sorry, "' . ($row['name'] ?? 'an item') . '" does not have enough stock any more. Please update your cart.');
                }
                $subtotal += (float) $row['price'] * $l['qty'];
            }
            $shipping = shipping_for($subtotal);
            $total = $subtotal + $shipping;

            $pdo->prepare('INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, subtotal, shipping, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$user['id'], $name, $email, $phone, $address, $subtotal, $shipping, $total]);
            $orderId = (int) $pdo->lastInsertId();

            $item = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            $take = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            foreach ($lines as $l) {
                $pid = (int) $l['product']['id'];
                $item->execute([$orderId, $pid, $l['qty'], $fresh[$pid]['price']]);
                $take->execute([$l['qty'], $pid]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            header('Location: order_success.php?order_id=' . $orderId);
            exit;
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getMessage();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong placing your order. Please try again.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="wrap"><h1>Checkout</h1></div></section>

<div class="wrap cart-page">
    <?php if ($errors): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <div class="cart-layout">
        <form method="post" action="checkout.php" class="panel form" id="checkout-form">
            <h2>Delivery details</h2>
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="<?= e($_POST['name'] ?? $user['name']) ?>" required autocomplete="name">
            <div class="form-row">
                <div><label for="email">Email</label><input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? $user['email']) ?>" required autocomplete="email"></div>
                <div><label for="phone">Phone</label><input type="tel" id="phone" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" required autocomplete="tel"></div>
            </div>
            <label for="address">Delivery address</label>
            <textarea id="address" name="address" rows="3" required autocomplete="street-address" placeholder="House number, street, postcode, city and state"><?= e($_POST['address'] ?? '') ?></textarea>

            <h2 style="margin-top:28px">Payment</h2>
            <div class="pay-option"><span class="pay-dot"></span><div><strong>Cash on delivery</strong><span>Pay the courier when your order arrives.</span></div></div>

            <button type="submit" class="btn btn-primary btn-lg" style="margin-top:22px">Place order</button>
        </form>

        <aside class="summary-card">
            <h2>Your order</h2>
            <ul class="mini-list">
                <?php foreach ($lines as $l): $p = $l['product']; ?>
                    <li>
                        <span class="mini-thumb"><?= product_image($p) ?><em><?= (int) $l['qty'] ?></em></span>
                        <span class="mini-name"><?= e($p['name']) ?></span>
                        <span><?= money($l['line_total']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <dl class="summary-list">
                <div><dt>Subtotal</dt><dd><?= money($sum['subtotal']) ?></dd></div>
                <div><dt>Delivery</dt><dd><?= $sum['shipping'] > 0 ? money($sum['shipping']) : 'Free' ?></dd></div>
                <div class="summary-total"><dt>Total</dt><dd><?= money($sum['total']) ?></dd></div>
            </dl>
        </aside>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
