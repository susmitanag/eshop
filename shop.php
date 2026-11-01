<?php
require __DIR__ . '/config.php';

$pageTitle = 'Shop';
$currentPage = 'shop';
$perPage = 12;

$categories = $pdo->query('SELECT c.*, COUNT(p.id) AS n FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.id')->fetchAll();

$categoryId = (int) ($_GET['category'] ?? 0);
$keyword = trim($_GET['q'] ?? '');
$min = ($_GET['min'] ?? '') !== '' ? max(0, (float) $_GET['min']) : null;
$max = ($_GET['max'] ?? '') !== '' ? max(0, (float) $_GET['max']) : null;
$sale = !empty($_GET['sale']);
$onlyNew = !empty($_GET['new']);
$inStock = !empty($_GET['instock']);
$sort = $_GET['sort'] ?? 'popular';
$page = max(1, (int) ($_GET['page'] ?? 1));

$where = [];
$params = [];
if ($categoryId > 0) { $where[] = 'p.category_id = ?'; $params[] = $categoryId; }
if ($keyword !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $like = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($min !== null) { $where[] = 'p.price >= ?'; $params[] = $min; }
if ($max !== null) { $where[] = 'p.price <= ?'; $params[] = $max; }
if ($sale) { $where[] = 'p.old_price IS NOT NULL AND p.old_price > p.price'; }
if ($onlyNew) { $where[] = 'p.is_new = 1'; }
if ($inStock) { $where[] = 'p.stock > 0'; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$orders = [
    'popular'    => 'p.is_featured DESC, p.id ASC',
    'newest'     => 'p.created_at DESC, p.id DESC',
    'price_low'  => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name'       => 'p.name ASC',
];
if (!isset($orders[$sort])) { $sort = 'popular'; }

$count = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
$count->execute($params);
$total = (int) $count->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id $whereSql ORDER BY {$orders[$sort]} LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$title = 'All products';
foreach ($categories as $c) { if ((int) $c['id'] === $categoryId) { $title = $c['name']; } }
if ($sale) { $title = 'Sale'; }
if ($onlyNew) { $title = 'New arrivals'; }
if ($keyword !== '') { $title = 'Results for "' . $keyword . '"'; }
$pageTitle = $title;

$hasFilters = $categoryId || $keyword !== '' || $min !== null || $max !== null || $sale || $onlyNew || $inStock;

require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <p class="breadcrumb"><a href="index.php">Home</a> / Shop</p>
        <h1><?= e($title) ?></h1>
        <p class="muted"><?= $total ?> <?= $total === 1 ? 'product' : 'products' ?></p>
    </div>
</section>

<div class="wrap shop-layout">
    <form class="filters" method="get" action="shop.php" data-autosubmit>
        <?php if ($keyword !== ''): ?><input type="hidden" name="q" value="<?= e($keyword) ?>"><?php endif; ?>
        <input type="hidden" name="sort" value="<?= e($sort) ?>">

        <div class="filter-group">
            <span class="group-label">Category</span>
            <div class="radio-list">
                <label><input type="radio" name="category" value="" <?= $categoryId === 0 ? 'checked' : '' ?>> All <small>(<?= array_sum(array_column($categories, 'n')) ?>)</small></label>
                <?php foreach ($categories as $c): ?>
                    <label><input type="radio" name="category" value="<?= (int) $c['id'] ?>" <?= $categoryId === (int) $c['id'] ? 'checked' : '' ?>> <?= e($c['name']) ?> <small>(<?= (int) $c['n'] ?>)</small></label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="filter-group">
            <span class="group-label">Price ($)</span>
            <div class="price-row">
                <input type="number" name="min" min="0" placeholder="Min" value="<?= $min !== null ? e($min) : '' ?>" aria-label="Minimum price">
                <span>to</span>
                <input type="number" name="max" min="0" placeholder="Max" value="<?= $max !== null ? e($max) : '' ?>" aria-label="Maximum price">
            </div>
        </div>
        <div class="filter-group">
            <label class="check"><input type="checkbox" name="sale" value="1" <?= $sale ? 'checked' : '' ?>> On sale</label>
            <label class="check"><input type="checkbox" name="new" value="1" <?= $onlyNew ? 'checked' : '' ?>> New arrivals</label>
            <label class="check"><input type="checkbox" name="instock" value="1" <?= $inStock ? 'checked' : '' ?>> In stock only</label>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Apply</button>
        <?php if ($hasFilters): ?><a href="shop.php" class="clear-link">Clear all filters</a><?php endif; ?>
    </form>

    <section>
        <div class="results-bar">
            <span class="muted">Showing <?= $total ? $offset + 1 : 0 ?> to <?= min($offset + $perPage, $total) ?> of <?= $total ?></span>
            <label class="sort-label">Sort by
                <select class="sort-select" data-sort>
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Popular</option>
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: low to high</option>
                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: high to low</option>
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A to Z</option>
                </select>
            </label>
        </div>

        <?php if (!$products): ?>
            <div class="empty-state">
                <h3>No products found</h3>
                <p>Try a different search or remove some filters.</p>
                <a href="shop.php" class="btn btn-primary">See all products</a>
            </div>
        <?php else: ?>
            <div class="product-grid product-grid-3">
                <?php foreach ($products as $product): require __DIR__ . '/includes/product_card.php'; endforeach; ?>
            </div>
            <?php if ($pages > 1): ?>
                <nav class="pagination" aria-label="Pages">
                    <?php if ($page > 1): ?><a href="shop.php?<?= e(build_query(['page' => $page - 1])) ?>">Previous</a><?php else: ?><span class="is-disabled">Previous</span><?php endif; ?>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <?php if ($i === $page): ?><span class="is-active" aria-current="page"><?= $i ?></span>
                        <?php else: ?><a href="shop.php?<?= e(build_query(['page' => $i])) ?>"><?= $i ?></a><?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $pages): ?><a href="shop.php?<?= e(build_query(['page' => $page + 1])) ?>">Next</a><?php else: ?><span class="is-disabled">Next</span><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
