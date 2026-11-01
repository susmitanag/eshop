<?php require_once __DIR__ . '/art.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Everly admin' : 'Everly admin' ?></title>
<link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
<header class="admin-header">
    <div class="wrap header-inner">
        <a href="index.php" class="logo logo-light"><img src="../assets/img/mark.svg" width="34" height="34" alt=""><span>Everly</span><em class="admin-tag">Admin</em></a>
        <nav class="main-nav admin-nav" aria-label="Admin">
            <a href="index.php" class="<?= ($adminPage ?? '') === 'dashboard' ? 'is-current' : '' ?>">Dashboard</a>
            <a href="products.php" class="<?= ($adminPage ?? '') === 'products' ? 'is-current' : '' ?>">Products</a>
            <a href="orders.php" class="<?= ($adminPage ?? '') === 'orders' ? 'is-current' : '' ?>">Orders</a>
            <a href="../index.php">View store</a>
            <a href="logout.php">Log out</a>
        </nav>
    </div>
</header>
<main class="wrap admin-main">
