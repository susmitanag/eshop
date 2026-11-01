<?php
require __DIR__ . '/config.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

$currentPage = 'shop';
if (!$product) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="wrap" style="padding:64px 0"><div class="empty-state"><h3>We could not find that product</h3><a class="btn btn-primary" href="shop.php">Back to the shop</a></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$related = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.category_id = ? AND p.id <> ? ORDER BY p.is_featured DESC, p.id LIMIT 4');
$related->execute([$product['category_id'], $product['id']]);
$relatedProducts = $related->fetchAll();

$onSale = !empty($product['old_price']) && (float) $product['old_price'] > (float) $product['price'];
$off = $onSale ? (int) round(100 - ((float) $product['price'] / (float) $product['old_price']) * 100) : 0;
$stock = (int) $product['stock'];
$inCart = (int) ($_SESSION['cart'][$product['id']] ?? 0);

$pageTitle = $product['name'];
require __DIR__ . '/includes/header.php';
?>

<div class="wrap">
    <p class="breadcrumb crumb-pad"><a href="index.php">Home</a> / <a href="shop.php?category=<?= (int) $product['category_id'] ?>"><?= e($product['category_name']) ?></a> / <?= e($product['name']) ?></p>

    <div class="product-detail">
        <div class="detail-art">
            <?= product_image($product, '', 'pimg pimg-large') ?>
            <?php if ($onSale): ?><span class="badge badge-sale">-<?= $off ?>%</span>
            <?php elseif (!empty($product['is_new'])): ?><span class="badge badge-new">New</span><?php endif; ?>
        </div>

        <div class="detail-info">
            <p class="product-cat"><?= e($product['category_name']) ?></p>
            <h1><?= e($product['name']) ?></h1>
            <p class="product-price product-price-large">
                <span class="price-now"><?= money((float) $product['price']) ?></span>
                <?php if ($onSale): ?><span class="price-old"><?= money((float) $product['old_price']) ?></span><span class="save-pill">Save <?= money((float) $product['old_price'] - (float) $product['price']) ?></span><?php endif; ?>
            </p>
            <p class="product-description"><?= e($product['description']) ?></p>

            <p class="stock-line <?= $stock === 0 ? 'is-out' : ($stock < 10 ? 'is-low' : 'is-ok') ?>">
                <span class="stock-dot"></span>
                <?php if ($stock === 0): ?>Sold out
                <?php elseif ($stock < 10): ?>Only <?= $stock ?> left
                <?php else: ?>In stock<?php endif; ?>
            </p>

            <?php if ($stock > 0): ?>
                <form method="post" action="add_to_cart.php" class="buy-form" id="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <div class="qty-stepper">
                        <button type="button" class="qty-btn" data-step="-1" aria-label="Decrease quantity">&minus;</button>
                        <input type="number" id="qty" name="qty" value="1" min="1" max="<?= $stock ?>" aria-label="Quantity">
                        <button type="button" class="qty-btn" data-step="1" aria-label="Increase quantity">&plus;</button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" id="add-to-cart-btn">Add to cart</button>
                </form>
                <p class="in-cart-note" id="add-to-cart-message" <?= $inCart ? '' : 'hidden' ?>><?= $inCart ? $inCart . ' in your cart. ' : '' ?><a href="cart.php">View cart</a></p>
            <?php endif; ?>

            <ul class="detail-perks">
                <li>Free delivery on orders over $75</li>
                <li>Pay when your order arrives</li>
                <li>7 day returns on unused items</li>
            </ul>
        </div>
    </div>

    <?php if ($relatedProducts): ?>
        <section class="related">
            <h2>You might also like</h2>
            <div class="product-grid product-grid-4">
                <?php foreach ($relatedProducts as $product): require __DIR__ . '/includes/product_card.php'; endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
