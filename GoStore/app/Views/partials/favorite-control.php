<?php if ($user && !isAdmin()): ?>
    <form method="post" class="product-favorite-form">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="product_favorite">
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <input type="hidden" name="favorite_action" value="<?= !empty($product['is_favorite']) ? 'remove' : 'add' ?>">
        <input type="hidden" name="return_url" value="<?= e($favoriteReturnUrl) ?>">
        <button class="favorite-toggle <?= !empty($product['is_favorite']) ? 'is-favorite' : '' ?>" type="submit" aria-pressed="<?= !empty($product['is_favorite']) ? 'true' : 'false' ?>" aria-label="<?= !empty($product['is_favorite']) ? 'Quitar de favoritos' : 'Agregar a favoritos' ?>" title="<?= !empty($product['is_favorite']) ? 'Quitar de favoritos' : 'Agregar a favoritos' ?>">
            <span aria-hidden="true"><?= !empty($product['is_favorite']) ? '♥' : '♡' ?></span>
            <?php if (empty($favoriteControlCompact)): ?><span><?= !empty($product['is_favorite']) ? 'Quitar de favoritos' : 'Agregar a favoritos' ?></span><?php endif; ?>
        </button>
    </form>
<?php elseif (!$user): ?>
    <a class="favorite-signin" href="index.php?page=login" aria-label="Inicia sesión para guardar favoritos" title="Inicia sesión para guardar favoritos">
        <span aria-hidden="true">♡</span>
        <?php if (empty($favoriteControlCompact)): ?><span>Inicia sesión para guardar favoritos</span><?php endif; ?>
    </a>
<?php endif; ?>
