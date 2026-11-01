<?php
require __DIR__ . '/../config.php';
require_admin();

$adminPage = 'products';
$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;
$p = [
    'name' => '', 'description' => '', 'price' => '', 'old_price' => '', 'stock' => '', 'category_id' => '',
    'art' => 'book', 'swatch' => '#3B4A6B', 'image' => '', 'is_new' => 0, 'is_featured' => 0,
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) { header('Location: products.php'); exit; }
    $p = array_merge($p, $existing);
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $oldPrice = trim($_POST['old_price'] ?? '');
    $stock = $_POST['stock'] ?? '';
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $art = $_POST['art'] ?? 'book';
    $swatch = trim($_POST['swatch'] ?? '#3B4A6B');
    $isNew = !empty($_POST['is_new']) ? 1 : 0;
    $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
    $removeImage = !empty($_POST['remove_image']);

    if ($name === '') $errors[] = 'Product name is required.';
    if (!is_numeric($price) || (float) $price < 0) $errors[] = 'Price must be a number that is zero or more.';
    if ($oldPrice !== '' && (!is_numeric($oldPrice) || (float) $oldPrice <= (float) $price)) $errors[] = 'The old price must be higher than the price, or left empty.';
    if (!ctype_digit((string) $stock)) $errors[] = 'Stock must be a whole number.';
    if ($categoryId <= 0) $errors[] = 'Please choose a category.';
    if (!isset(art_types()[$art])) $art = 'book';
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $swatch)) $swatch = '#3B4A6B';

    // Unique slug
    $base = slugify($name) ?: 'product';
    $slug = $base;
    $n = 2;
    $chk = $pdo->prepare('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?');
    while (true) {
        $chk->execute([$slug, $id]);
        if (!(int) $chk->fetchColumn()) break;
        $slug = $base . '-' . $n++;
    }

    // Photo upload: JPG, PNG or WebP, up to 2 MB. The file type is checked from the file itself.
    $imageName = $p['image'];
    if ($removeImage) { $imageName = ''; }
    $upload = $_FILES['photo'] ?? null;
    if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($upload['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The photo could not be uploaded. Try a smaller file.';
        } elseif ($upload['size'] > 2 * 1024 * 1024) {
            $errors[] = 'The photo is too big. Please use a file under 2 MB.';
        } else {
            $info = @getimagesize($upload['tmp_name']);
            $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
            if (!$info || !isset($types[$info[2]])) {
                $errors[] = 'The photo must be a JPG, PNG or WebP image.';
            } else {
                $newName = $slug . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $types[$info[2]];
                $dir = __DIR__ . '/../assets/img/products/';
                if (!is_dir($dir)) { mkdir($dir, 0755, true); }
                if (!$errors && move_uploaded_file($upload['tmp_name'], $dir . $newName)) {
                    $imageName = $newName;
                } elseif (!$errors) {
                    $errors[] = 'Could not save the photo on the server.';
                }
            }
        }
    }

    $p = array_merge($p, [
        'name' => $name, 'description' => $description, 'price' => $price, 'old_price' => $oldPrice, 'stock' => $stock,
        'category_id' => $categoryId, 'art' => $art, 'swatch' => $swatch, 'is_new' => $isNew, 'is_featured' => $isFeatured,
    ]);

    if (!$errors) {
        $oldFile = $isEdit ? ($existing['image'] ?? '') : '';
        $oldVal = $oldPrice === '' ? null : $oldPrice;
        if ($isEdit) {
            $pdo->prepare('UPDATE products SET name=?, slug=?, description=?, price=?, old_price=?, stock=?, category_id=?, art=?, swatch=?, image=?, is_new=?, is_featured=? WHERE id=?')
                ->execute([$name, $slug, $description, $price, $oldVal, $stock, $categoryId, $art, $swatch, $imageName ?: null, $isNew, $isFeatured, $id]);
            $_SESSION['admin_flash'] = 'Product saved.';
        } else {
            $pdo->prepare('INSERT INTO products (name, slug, description, price, old_price, stock, category_id, art, swatch, image, is_new, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$name, $slug, $description, $price, $oldVal, $stock, $categoryId, $art, $swatch, $imageName ?: null, $isNew, $isFeatured]);
            $_SESSION['admin_flash'] = 'Product created.';
        }
        // Remove the old photo file if it was replaced or removed.
        if ($oldFile && $oldFile !== $imageName && is_file(__DIR__ . '/../assets/img/products/' . $oldFile)) {
            @unlink(__DIR__ . '/../assets/img/products/' . $oldFile);
        }
        header('Location: products.php');
        exit;
    }
}

$pageTitle = $isEdit ? 'Edit product' : 'Add a product';
require __DIR__ . '/../includes/admin_header.php';
$previewProduct = array_merge($p, ['id' => $id, 'name' => $p['name'] ?: 'Preview']);
?>
<p class="breadcrumb"><a href="products.php">Products</a> / <?= $isEdit ? 'Edit' : 'New' ?></p>
<h1><?= e($pageTitle) ?></h1>

<?php if ($errors): ?>
    <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" action="product_form.php<?= $isEdit ? '?id=' . $id : '' ?>" enctype="multipart/form-data" class="panel form admin-form">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= e($p['name']) ?>" required>

    <div class="form-row">
        <div>
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Choose a category</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $p['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" min="0" step="1" value="<?= e($p['stock']) ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label for="price">Price ($)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= e($p['price']) ?>" required>
        </div>
        <div>
            <label for="old_price">Old price ($), for a sale <span class="muted">(optional)</span></label>
            <input type="number" id="old_price" name="old_price" step="0.01" min="0" value="<?= e($p['old_price']) ?>">
        </div>
    </div>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4"><?= e($p['description']) ?></textarea>

    <label for="photo">Product photo <span class="muted">(optional)</span></label>
    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
    <p class="hint">JPG, PNG or WebP, up to 2 MB. Square photos look best. With no photo, a drawn picture is used.</p>
    <?php if (!empty($p['image'])): ?>
        <div class="preview-box"><?= product_image($previewProduct, '../') ?></div>
        <label style="display:flex;gap:10px;align-items:center;font-weight:600"><input type="checkbox" name="remove_image" value="1" style="width:auto"> Remove this photo</label>
    <?php endif; ?>

    <div class="form-row">
        <div>
            <label for="art">Drawn picture type <span class="muted">(used when there is no photo)</span></label>
            <select id="art" name="art">
                <?php foreach (art_types() as $k => $label): ?>
                    <option value="<?= e($k) ?>" <?= $p['art'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="swatch">Picture colour</label>
            <input type="color" id="swatch" name="swatch" value="<?= e($p['swatch']) ?>">
        </div>
    </div>

    <div class="checks">
        <label><input type="checkbox" name="is_new" value="1" <?= !empty($p['is_new']) ? 'checked' : '' ?>> Show a "New" badge</label>
        <label><input type="checkbox" name="is_featured" value="1" <?= !empty($p['is_featured']) ? 'checked' : '' ?>> Feature on the home page</label>
    </div>

    <div style="margin-top:22px;display:flex;gap:12px">
        <button type="submit" class="btn btn-primary btn-lg"><?= $isEdit ? 'Save changes' : 'Create product' ?></button>
        <a href="products.php" class="btn btn-light btn-lg">Cancel</a>
    </div>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
