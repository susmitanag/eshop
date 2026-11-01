<?php
require __DIR__ . '/config.php';

$pageTitle = '';
$currentPage = 'home';

$base = 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id';
$featured = $pdo->query($base . ' WHERE p.is_featured = 1 AND p.stock > 0 ORDER BY p.id LIMIT 8')->fetchAll();
$newest = $pdo->query($base . ' WHERE p.is_new = 1 ORDER BY p.created_at DESC, p.id DESC LIMIT 4')->fetchAll();
$saleItems = $pdo->query($base . ' WHERE p.old_price IS NOT NULL AND p.old_price > p.price ORDER BY (p.old_price - p.price) DESC LIMIT 4')->fetchAll();
$heroProducts = $pdo->query('SELECT * FROM products WHERE slug IN ("aero-wireless-headphones", "indigo-denim-jacket", "hana-ceramic-mug-set") ORDER BY id')->fetchAll();

$cats = $pdo->query(
    'SELECT c.*, COUNT(p.id) AS n, (SELECT id FROM products WHERE category_id = c.id AND is_featured = 1 ORDER BY id LIMIT 1) AS pid
     FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.id'
)->fetchAll();
$catProducts = [];
foreach ($cats as $c) {
    $st = $pdo->prepare('SELECT * FROM products WHERE category_id = ? ORDER BY is_featured DESC, id LIMIT 1');
    $st->execute([$c['id']]);
    $catProducts[$c['id']] = $st->fetch();
}

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="wrap hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">New this week</span>
            <h1>Good things for everyday life</h1>
            <p class="hero-lead">Electronics, clothes, home goods and books at fair prices. Free delivery over $75, and you pay when your order arrives.</p>
            <div class="hero-actions">
                <a href="shop.php" class="btn btn-primary btn-lg">Shop now</a>
                <a href="shop.php?sale=1" class="btn btn-light btn-lg">See the sale</a>
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            <?php foreach ($heroProducts as $i => $hp): ?>
                <div class="hero-tile hero-tile-<?= $i + 1 ?>"><?= product_image($hp) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="perks">
    <div class="wrap perks-grid">
        <div><strong>Free delivery</strong><span>On orders over $75</span></div>
        <div><strong>Pay on delivery</strong><span>No card needed to order</span></div>
        <div><strong>7 day returns</strong><span>Changed your mind? No problem</span></div>
        <div><strong>Real support</strong><span>We answer within a day</span></div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="section-head"><h2>Shop by category</h2><a class="link-arrow" href="shop.php">All products &rarr;</a></div>
        <div class="cat-grid">
            <?php foreach ($cats as $c): $cp = $catProducts[$c['id']] ?? null; ?>
                <a class="cat-card" href="shop.php?category=<?= (int) $c['id'] ?>">
                    <div class="cat-art"><?= $cp ? product_image($cp) : '' ?></div>
                    <div class="cat-label"><strong><?= e($c['name']) ?></strong><span><?= (int) $c['n'] ?> products</span></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="wrap">
        <div class="section-head"><h2>Popular right now</h2><a class="link-arrow" href="shop.php">See everything &rarr;</a></div>
        <div class="product-grid">
            <?php foreach ($featured as $product): require __DIR__ . '/includes/product_card.php'; endforeach; ?>
        </div>
    </div>
</section>

<?php if ($saleItems): ?>
<section class="section">
    <div class="wrap">
        <div class="banner banner-sale">
            <div>
                <span class="eyebrow eyebrow-light">Limited time</span>
                <h2>Up to 20% off selected items</h2>
                <p>Watches, keyboards, bags and blankets are all marked down this month.</p>
            </div>
            <a href="shop.php?sale=1" class="btn btn-accent btn-lg">Shop the sale</a>
        </div>
        <div class="product-grid product-grid-4" style="margin-top:28px">
            <?php foreach ($saleItems as $product): require __DIR__ . '/includes/product_card.php'; endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($newest): ?>
<section class="section section-tint">
    <div class="wrap">
        <div class="section-head"><h2>New arrivals</h2><a class="link-arrow" href="shop.php?new=1">See all new &rarr;</a></div>
        <div class="product-grid product-grid-4">
            <?php foreach ($newest as $product): require __DIR__ . '/includes/product_card.php'; endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
