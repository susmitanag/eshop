<?php
require __DIR__ . '/config.php';

$isAjax = (
    ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
    || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
);

function respond($isAjax, array $payload, string $redirectUrl) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
    header('Location: ' . $redirectUrl);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond($isAjax, ['success' => false, 'error' => 'Invalid request.'], 'shop.php');
}

$productId = (int) ($_POST['product_id'] ?? 0);
$qty = max(1, (int) ($_POST['qty'] ?? 1));

$stmt = $pdo->prepare('SELECT id, name, stock FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    respond($isAjax, ['success' => false, 'error' => 'Product not found.'], 'shop.php');
}
if ((int) $product['stock'] < 1) {
    respond($isAjax, ['success' => false, 'error' => 'Sorry, this product is sold out.'], 'product.php?id=' . $productId);
}

$currentQty = $_SESSION['cart'][$productId] ?? 0;
$newQty = min($currentQty + $qty, (int) $product['stock']);
$_SESSION['cart'][$productId] = $newQty;

respond($isAjax, [
    'success'      => true,
    'product_name' => $product['name'],
    'cart_qty'     => $newQty,
    'cart_count'   => cart_count(),
    'capped'       => ($currentQty + $qty) > (int) $product['stock'],
], 'cart.php');
