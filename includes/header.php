<?php
require_once __DIR__ . '/art.php';
$pageTitle = $pageTitle ?? '';
$currentPage = $currentPage ?? '';
$navCats = $pdo->query('SELECT id, name, slug FROM categories ORDER BY id')->fetchAll();
$headerQ = trim($_GET['q'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle !== '' ? htmlspecialchars($pageTitle) . ' | Everly' : 'Everly | Everyday goods, delivered' ?></title>
<meta name="description" content="Everly is an online store for electronics, fashion, home goods and books. Free delivery on orders over $75.">
<link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="promo-bar">Free delivery on orders over $75 <span class="promo-dot"></span> Pay when it arrives</div>
<header class="site-header">
    <div class="wrap header-inner">
        <form class="header-search" action="shop.php" method="get" role="search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input type="search" name="q" value="<?= htmlspecialchars($headerQ) ?>" placeholder="Search for products" aria-label="Search for products">
        </form>
        <a href="index.php" class="logo" aria-label="Everly home">
            <img src="assets/img/mark.svg" width="38" height="38" alt="">
            <span>Everly</span>
        </a>
        <nav class="main-nav" aria-label="Main">
            <a href="shop.php" class="<?= $currentPage === 'shop' ? 'is-current' : '' ?>">Shop</a>
            <?php if (is_logged_in()): ?>
                <?php if (is_admin()): ?><a href="admin/index.php">Admin</a><?php endif; ?>
                <a href="my_orders.php" class="<?= $currentPage === 'orders' ? 'is-current' : '' ?>">My orders</a>
                <a href="logout.php">Log out</a>
            <?php else: ?>
                <a href="login.php" class="<?= $currentPage === 'login' ? 'is-current' : '' ?>">Log in</a>
            <?php endif; ?>
            <a href="cart.php" class="cart-link" aria-label="Cart">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h14l-1 12H6zM9 8V6a3 3 0 016 0v2"/></svg>
                <span class="cart-badge" id="js-cart-badge"><?= cart_count() ?></span>
            </a>
        </nav>
    </div>
    <div class="cat-bar">
        <div class="wrap cat-bar-inner">
            <?php foreach ($navCats as $nc): ?>
                <a href="shop.php?category=<?= (int) $nc['id'] ?>"><?= htmlspecialchars($nc['name']) ?></a>
            <?php endforeach; ?>
            <a href="shop.php?sale=1" class="sale-link">Sale</a>
        </div>
    </div>
</header>
<main id="main">
