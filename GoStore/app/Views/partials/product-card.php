<article class="product-card" <?= isset($product['search_match']) && ((int)$product['search_match'] !== 1 || ($categoryFilter !== '' && $product['category'] !== $categoryFilter)) ? 'hidden' : '' ?> data-category="<?= e($product['category']) ?>" data-search="<?= e($product['name'] . ' ' . $product['category'] . ' ' . $product['description']) ?>">
    <a class="product-image" href="index.php?product=<?= (int)$product['id'] ?>">
        <?php if ($product['image']): ?><img src="uploads/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy" decoding="async"><?php else: ?><div class="image-placeholder">✦</div><?php endif; ?>
        <span class="product-rating">★ <?= number_format((float)$product['avg_rating'], 1) ?></span>
    </a>
    <div class="product-card-body">
        <span class="product-category"><?= e($product['category']) ?></span>
        <a class="product-name" href="index.php?product=<?= (int)$product['id'] ?>"><?= e($product['name']) ?></a>
        <div class="product-card-bottom">
            <strong><?= e(formatMoney((float)$product['price'], $currency)) ?></strong>
            <span><?= (int)$product['stock'] > 0 ? (int)$product['stock'] . ' disponibles' : 'Agotado' ?></span>
        </div>
        <div class="product-card-actions">
            <?php $favoriteControlCompact = true; require __DIR__ . '/favorite-control.php'; unset($favoriteControlCompact); ?>
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="cart_add">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <button class="add-button" <?= (int)$product['stock'] < 1 ? 'disabled' : '' ?>><?= (int)$product['stock'] > 0 ? '+ Añadir al carrito' : 'Sin stock' ?></button>
            </form>
        </div>
    </div>
</article>
