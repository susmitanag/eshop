<?php
// Expects $product (a products row). Optional $catName.
$onSale = !empty($product['old_price']) && (float) $product['old_price'] > (float) $product['price'];
$off = $onSale ? (int) round(100 - ((float) $product['price'] / (float) $product['old_price']) * 100) : 0;
$out = (int) $product['stock'] <= 0;
?>
<article class="product-card">
    <a class="product-thumb" href="product.php?id=<?= (int) $product['id'] ?>" aria-label="<?= e($product['name']) ?>">
        <?= product_image($product) ?>
        <?php if ($out): ?><span class="badge badge-out">Sold out</span>
        <?php elseif ($onSale): ?><span class="badge badge-sale">-<?= $off ?>%</span>
        <?php elseif (!empty($product['is_new'])): ?><span class="badge badge-new">New</span><?php endif; ?>
    </a>
    <div class="product-info">
        <?php if (!empty($product['category_name'])): ?><p class="product-cat"><?= e($product['category_name']) ?></p><?php endif; ?>
        <h3><a href="product.php?id=<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3>
        <p class="product-price">
            <span class="price-now"><?= money((float) $product['price']) ?></span>
            <?php if ($onSale): ?><span class="price-old"><?= money((float) $product['old_price']) ?></span><?php endif; ?>
        </p>
    </div>
</article>
