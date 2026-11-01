<?php
require __DIR__ . '/../config.php';
require_admin();

$pageTitle = 'Products';
$adminPage = 'products';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
    $stmt->execute([$id]);
    if ((int) $stmt->fetchColumn() > 0) {
        $_SESSION['admin_flash'] = 'This product is in existing orders, so it cannot be deleted. Set its stock to 0 instead.';
    } else {
        $img = $pdo->prepare('SELECT image FROM products WHERE id = ?');
        $img->execute([$id]);
        $file = $img->fetchColumn();
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        if ($file && is_file(__DIR__ . '/../assets/img/products/' . $file)) {
            @unlink(__DIR__ . '/../assets/img/products/' . $file);
        }
        $_SESSION['admin_flash'] = 'Product deleted.';
    }
    header('Location: products.php');
    exit;
}

$flash = $_SESSION['admin_flash'] ?? '';
unset($_SESSION['admin_flash']);

$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page-header">
    <h1>Products</h1>
    <a href="product_form.php" class="btn btn-primary">Add a product</a>
</div>

<?php if ($flash): ?><div class="alert alert-ok"><?= e($flash) ?></div><?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><div class="cell-product"><span class="admin-thumb"><?= product_image($p, '../') ?></span><a href="../product.php?id=<?= (int) $p['id'] ?>"><?= e($p['name']) ?></a></div></td>
                <td><?= e($p['category_name'] ?? '-') ?></td>
                <td><?= money((float) $p['price']) ?><?php if (!empty($p['old_price'])): ?> <span class="price-old"><?= money((float) $p['old_price']) ?></span><?php endif; ?></td>
                <td><?= (int) $p['stock'] ?></td>
                <td class="admin-actions">
                    <a href="product_form.php?id=<?= (int) $p['id'] ?>">Edit</a>
                    <form method="post" action="products.php" data-confirm="Delete this product? This cannot be undone.">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
});
</script>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
