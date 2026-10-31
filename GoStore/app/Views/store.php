<?php
/** @var mixed $categoryFilter */
/** @var mixed $search */
/** @var mixed $user */
/** @var mixed $unreadCount */
/** @var mixed $notifications */
/** @var mixed $cartCount */
/** @var mixed $categories */
/** @var mixed $page */
/** @var mixed $flash */
/** @var mixed $stats */
/** @var mixed $editing */
/** @var mixed $allProducts */
/** @var mixed $carouselImages */
/** @var mixed $orders */
/** @var mixed $matchingProducts */
/** @var mixed $products */
/** @var mixed $featured */
/** @var mixed $featuredReviews */
/** @var mixed $selectedProduct */
/** @var mixed $reviews */
/** @var mixed $myOrders */
/** @var mixed $cartItems */
/** @var mixed $cartTotal */
/** @var mixed $whatsappMessage */
/** @var mixed $adminEmail */
/** @var mixed $currency */
/** @var mixed $showUserGuide */
/** @var mixed $carouselSlideCount */
$favoriteReturnUrl = 'index.php';
if (($page ?? '') === 'favorites') {
    $favoriteReturnUrl = 'index.php?page=favorites';
} elseif (!empty($selectedProduct)) {
    $favoriteProductQuery = ['product' => (int)$selectedProduct['id']];
    if (!empty($comparisonProduct)) {
        $favoriteProductQuery['compare'] = (int)$comparisonProduct['id'];
    }
    $favoriteReturnUrl = 'index.php?' . http_build_query($favoriteProductQuery) . '#product-title';
} elseif (($page ?? '') === 'store' && (($search ?? '') !== '' || ($categoryFilter ?? '') !== '')) {
    $favoriteFilters = array_filter(
        ['q' => $search ?? '', 'category' => $categoryFilter ?? ''],
        static fn(string $value): bool => $value !== ''
    );
    $favoriteReturnUrl = 'index.php?' . http_build_query($favoriteFilters, '', '&', PHP_QUERY_RFC3986);
}
?>
<!doctype html>
<html lang="<?= e($language ?? 'es') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fb">
    <meta name="description" content="GoStore: compra de todo en un solo lugar, con seguimiento de pedidos y pagos con Yape, Plin y BBVA.">
    <title>GoStore | Compra de todo en un solo lugar</title>
    <link rel="stylesheet" href="style.css?v=26">
</head>
<body data-page="<?= e($page) ?>" data-category="<?= e($categoryFilter) ?>" data-theme="<?= e($theme ?? 'violeta') ?>" data-lang="<?= e($language ?? 'es') ?>" data-user-authenticated="<?= $user ? 'true' : 'false' ?>">
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs><symbol id="i-home" viewBox="0 0 24 24"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/></symbol><symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol><symbol id="i-image" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.7"/><path d="M21 16l-5-5-8 9"/></symbol><symbol id="i-receipt" viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/></symbol><symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></symbol><symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></symbol><symbol id="i-settings" viewBox="0 0 24 24"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></symbol><symbol id="i-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.01"/></symbol><symbol id="i-logout" viewBox="0 0 24 24"><path d="M9 4H5v16h4"/><path d="M16 8l4 4-4 4M20 12H9"/></symbol><symbol id="i-cart" viewBox="0 0 24 24"><path d="M3 4h2l2.4 11h10.2l2-8H6.2"/><circle cx="9" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/></symbol><symbol id="i-heart" viewBox="0 0 24 24"><path d="M12 20s-8-5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 9c0 6-8 11-8 11z"/></symbol><symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/></symbol><symbol id="i-trend" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></symbol><symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol><symbol id="i-check" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></symbol><symbol id="i-truck" viewBox="0 0 24 24"><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></symbol></defs></svg>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<header class="topbar">
    <button class="menu-toggle" type="button" aria-controls="store-menu" aria-expanded="false" aria-label="Abrir menú">☰<span>menu</span></button>
    <a class="brand" href="index.php"><span class="brand-mark">G</span><span>Go<span class="brand-accent">Store</span><small>TODO, EN UN SOLO LUGAR</small></span></a>
    <?php if (!isAdmin() || $page === 'store'): ?>
        <form class="search-form" action="index.php" method="get">
            <input type="hidden" name="page" value="store">
            <span aria-hidden="true"><svg class="ico" aria-hidden="true"><use href="#i-search"/></svg></span><input type="search" name="q" value="<?= e($search) ?>" placeholder="¿Buscar?" aria-label="Buscar productos">
            <button class="search-button" type="submit">Buscar</button>
        </form>
    <?php endif; ?>
    <nav class="top-actions">
        <?php if ($user): ?>
            <a href="index.php?page=profile" class="nav-user"><span class="avatar"><?= e(mb_strtoupper(mb_substr($user['username'], 0, 1))) ?></span><span>Hola, <b><?= e($user['username']) ?></b></span></a>
        <?php else: ?>
            <a class="top-link" href="index.php?page=login">Ingresar</a><a class="button button-small" href="index.php?page=register">Crear cuenta</a>
        <?php endif; ?>
        <?php if ($user): ?>
            <details class="notification-menu">
                <summary class="notification-trigger" aria-label="Notificaciones<?= $unreadCount > 0 ? ', hay notificaciones nuevas' : '' ?>">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                        <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.5 21h5M10 3.5a2 2 0 0 1 4 0M3.2 7 2 6.3M20.8 7l1.2-.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                    <?php if ($unreadCount > 0): ?><span class="notification-indicator" aria-hidden="true"></span><?php endif; ?>
                </summary>
                <section class="notification-panel" aria-label="Notificaciones recientes">
                    <div class="notification-panel-heading"><b>Notificaciones</b><small><?= (int)$unreadCount > 0 ? 'Tienes novedades' : 'Actividad reciente' ?></small></div>
                    <?php if (!$notifications): ?>
                        <p class="notification-empty">Todavía no tienes notificaciones.</p>
                    <?php else: ?>
                        <div class="notification-list">
                            <?php foreach ($notifications as $notification): ?>
                                <form method="post">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="notification_read">
                                    <input type="hidden" name="notification_id" value="<?= (int)$notification['id'] ?>">
                                    <div class="notification-item <?= $notification['read_at'] === null ? 'unread' : '' ?>">
                                        <span class="notification-icon <?= $notification['type'] === 'order' ? 'order' : 'product' ?>" aria-hidden="true"><?= $notification['type'] === 'order' ? '↗' : '+' ?></span>
                                        <span class="notification-copy"><b><?= $notification['type'] === 'order' ? 'Nuevo pedido' : 'Nuevo producto' ?></b><span><?= e($notification['message']) ?></span><small><?= e(date('d/m/Y H:i', strtotime($notification['created_at']))) ?></small></span>
                                        <button class="notification-go" type="submit">Ir <span aria-hidden="true">→</span></button>
                                    </div>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </details>
        <?php endif; ?>
        <?php if (!isAdmin()): ?><a class="cart-link" href="index.php?page=cart"><span><svg class="ico" aria-hidden="true"><use href="#i-cart"/></svg></span><span>Carrito</span><b><?= (int)$cartCount ?></b></a><?php endif; ?>
    </nav>
</header>

<div class="layout">
    <div class="menu-backdrop" data-close-menu hidden></div>
    <aside class="sidebar" id="store-menu" aria-label="Menú de navegación" aria-hidden="true" inert>
        <div class="sidebar-top"><b>Menú</b><button class="menu-close" type="button" data-close-menu aria-label="Cerrar menú">×</button></div>
        <?php if (isAdmin()): ?>
            <div class="side-heading"><?= isSuperAdmin() ? 'SUPERADMINISTRACIÓN' : 'ADMINISTRACIÓN' ?></div>
            <?php if (isSuperAdmin()): ?><a class="side-link <?= $page === 'superadmin' ? 'active' : '' ?>" href="index.php?page=superadmin"><span><svg class="ico" aria-hidden="true"><use href="#i-shield"/></svg></span> Control SuperAdmin</a><?php endif; ?>
            <a class="side-link <?= $page === 'admin' ? 'active' : '' ?>" href="index.php?page=admin"><span><svg class="ico" aria-hidden="true"><use href="#i-home"/></svg></span> Resumen</a>
            <?php if ($adminPageAccess['admin_products']): ?><a class="side-link <?= $page === 'admin-products' ? 'active' : '' ?>" href="index.php?page=admin-products"><span><svg class="ico" aria-hidden="true"><use href="#i-grid"/></svg></span> Productos</a><?php endif; ?>
            <?php if ($adminPageAccess['admin_carousel']): ?><a class="side-link <?= $page === 'admin-carousel' ? 'active' : '' ?>" href="index.php?page=admin-carousel"><span><svg class="ico" aria-hidden="true"><use href="#i-image"/></svg></span> Carrusel</a><?php endif; ?>
            <?php if ($adminPageAccess['admin_orders']): ?><a class="side-link <?= $page === 'admin-orders' ? 'active' : '' ?>" href="index.php?page=admin-orders"><span><svg class="ico" aria-hidden="true"><use href="#i-receipt"/></svg></span> Pedidos</a><?php endif; ?>
            <?php if ($adminPageAccess['admin_users']): ?><a class="side-link <?= $page === 'admin-users' ? 'active' : '' ?>" href="index.php?page=admin-users"><span><svg class="ico" aria-hidden="true"><use href="#i-user"/></svg></span> Usuarios</a><?php endif; ?>
            <a class="side-link <?= $page === 'store' ? 'active' : '' ?>" href="index.php?page=store"><span><svg class="ico" aria-hidden="true"><use href="#i-search"/></svg></span> Ver productos</a>
        <?php else: ?>
            <div class="side-heading">EXPLORA</div>
            <a class="side-link <?= $page === 'store' && $categoryFilter === '' ? 'active' : '' ?>" href="index.php" data-category-filter=""><span><svg class="ico" aria-hidden="true"><use href="#i-home"/></svg></span> Todo</a>
            <div class="side-heading category-heading">CATEGORÍAS</div>
            <?php if (!$categories): ?><p class="empty-categories">Pronto verás categorías.</p><?php endif; ?>
            <?php foreach ($categories as $category): ?>
                <a class="side-link <?= $categoryFilter === $category ? 'active' : '' ?>" href="index.php?category=<?= rawurlencode($category) ?>" data-category-filter="<?= e($category) ?>"><span class="category-dot"></span><?= e($category) ?></a>
            <?php endforeach; ?>
            <div class="side-divider"></div>
        <?php endif; ?>
        <?php if ($user): ?>
            <?php if (!isAdmin()): ?><a class="side-link" href="index.php?page=orders"><span><svg class="ico" aria-hidden="true"><use href="#i-receipt"/></svg></span> Mis pedidos</a><?php endif; ?>
            <?php if (!isAdmin()): ?><a class="side-link <?= $page === 'favorites' ? 'active' : '' ?>" href="index.php?page=favorites"><span><svg class="ico" aria-hidden="true"><use href="#i-heart"/></svg></span> Mis favoritos</a><?php endif; ?>
            <a class="side-link" href="index.php?page=profile"><span><svg class="ico" aria-hidden="true"><use href="#i-user"/></svg></span> Mi perfil</a>
            <a class="side-link <?= $page === 'settings' ? 'active' : '' ?>" href="index.php?page=settings"><span><svg class="ico" aria-hidden="true"><use href="#i-settings"/></svg></span> Ajustes</a>
            <?php if (!isAdmin()): ?><form method="post" class="logout-form"><?= csrfField() ?><input type="hidden" name="action" value="guide_show"><button class="side-link" type="submit"><span><svg class="ico" aria-hidden="true"><use href="#i-info"/></svg></span> Ver guía</button></form><?php endif; ?>
            <form method="post" class="logout-form"><?= csrfField() ?><input type="hidden" name="action" value="logout"><button class="side-link logout-button" type="submit"><span><svg class="ico" aria-hidden="true"><use href="#i-logout"/></svg></span> Cerrar sesión</button></form>
        <?php else: ?>
            <div class="sidebar-promo"><div class="promo-icon">✦</div><b>Compra fácil y seguro</b><p>Inicia sesión para seguir tus pedidos y guardar tus reseñas.</p><a href="index.php?page=login">Iniciar sesión →</a></div>
        <?php endif; ?>
        <div class="sidebar-footer">© <?= date('Y') ?> GoStore<br>Compra local, compra mejor.</div>
    </aside>

    <main class="main-content" id="contenido" tabindex="-1">
        <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>" aria-live="<?= $flash['type'] === 'error' ? 'assertive' : 'polite' ?>"><span class="alert-icon" aria-hidden="true"><?= $flash['type'] === 'error' ? '!' : '✓' ?></span><span class="alert-message"><?= e($flash['text']) ?></span><button class="alert-dismiss" type="button" aria-label="Cerrar aviso">×</button></div><?php endif; ?>

        <?php if ($page === 'login' || $page === 'register'): ?>
            <section class="auth-wrap">
                <div class="auth-card">
                    <div class="auth-visual"><span class="auth-spark">✳</span><div><span class="eyebrow">BIENVENIDO A GOSTORE</span><h2>Lo que buscas,<br><em>está aquí.</em></h2><p>Descubre productos seleccionados y compra con confianza.</p></div><div class="auth-trust">✦ &nbsp; Compra simple · Atención cercana</div></div>
                    <div class="auth-form-pane">
                        <span class="eyebrow"><?= $page === 'login' ? 'QUÉ BUENO VERTE' : 'ÚNETE A LA COMUNIDAD' ?></span>
                        <h1><?= $page === 'login' ? 'Inicia sesión' : 'Crea tu cuenta' ?></h1>
                        <p class="muted"><?= $page === 'login' ? 'Ingresa tus datos para continuar.' : 'Solo te tomará un minuto.' ?></p>
                        <form method="post" class="form-stack">

                            
                            <?= csrfField() ?><input type="hidden" name="action" value="<?= $page === 'login' ? 'login' : 'register' ?>">
                            <?php if ($page === 'register'): ?>
                                <label>Nombre completo<input required name="full_name" autocomplete="name" placeholder="nombre"></label>
                                <label>Nombre de usuario<input required name="username" minlength="3" autocomplete="username" placeholder="usuario"></label>
                                <label>Correo electrónico<input required type="email" name="email" maxlength="190" autocomplete="email" placeholder="nombre@ejemplo.com"></label>
                            <?php else: ?>
                                <label>Usuario o correo<input required name="identity" autocomplete="username" placeholder="correo"></label>
                            <?php endif; ?>
                            <label>Contraseña<div class="password-control"><input required type="password" name="password" minlength="8" autocomplete="<?= $page === 'login' ? 'current-password' : 'new-password' ?>" placeholder="minimo 8 caracteres"><button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false"><svg class="password-eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.2 0 2.3-.3 3.3-.7"/></svg></button></div></label>
                            <button class="button button-primary button-wide" type="submit"><?= $page === 'login' ? 'Ingresar a mi cuenta' : 'Crear mi cuenta' ?> <span>→</span></button>
                        </form>
                        <p class="auth-switch"><?= $page === 'login' ? '¿Aún no tienes cuenta?' : '¿Ya tienes una cuenta?' ?> <a href="index.php?page=<?= $page === 'login' ? 'register' : 'login' ?>"><?= $page === 'login' ? 'Regístrate' : 'Inicia sesión' ?></a></p>
                    </div>
                </div>
            </section>

        <?php elseif (in_array($page, ['admin', 'admin-products', 'admin-carousel', 'admin-orders', 'admin-users', 'superadmin'], true)): ?>
            <?php if ($page !== 'admin'): ?>
                <section class="page-heading admin-heading <?= $page === 'superadmin' ? 'superadmin-heading' : '' ?>"><div><span class="eyebrow"><?= $page === 'superadmin' ? 'CONTROL TOTAL DEL SISTEMA' : 'CENTRO DE CONTROL' ?></span><h1><?= $page === 'superadmin' ? 'Panel SuperAdmin' : ['admin-products' => 'Productos', 'admin-carousel' => 'Carrusel', 'admin-orders' => 'Pedidos', 'admin-users' => 'Usuarios'][$page] ?></h1><p><?= $page === 'superadmin' ? 'Supervisa la actividad, protege las operaciones y mantén tu tienda en orden.' : 'Gestiona tu tienda y mantén cada pedido en movimiento.' ?></p></div><span class="admin-pill"><i></i> <?= $page === 'superadmin' ? 'SuperAdmin' : 'Administrador' ?></span></section>
            <?php endif; ?>
            <?php if (!in_array($page, ['admin-users', 'superadmin'], true)): ?>
                <div class="form-actions admin-export-actions">
                    <a class="button button-outline" href="index.php?export=excel&amp;page=<?= e($page) ?>">Exportar Excel (CSV)</a>
                    <a class="button button-outline" href="index.php?export=pdf&amp;page=<?= e($page) ?>">Exportar PDF</a>
                </div>
            <?php endif; ?>
            <?php if ($page === 'superadmin'): ?>
            <div class="superadmin-dashboard">
            <section class="stats-grid">
                <article class="stat-card"><span>Usuarios activos</span><b><?= (int)$stats['users'] ?></b><small>cuentas registradas</small><i aria-hidden="true"><svg class="ico"><use href="#i-user"/></svg></i></article>
                <article class="stat-card"><span>Cuentas eliminadas</span><b><?= (int)$stats['deleted_users'] ?></b><small>histórico de auditoría</small><i aria-hidden="true"><svg class="ico"><use href="#i-user"/></svg></i></article>
                <article class="stat-card"><span>Pedidos cancelados</span><b><?= (int)$stats['cancelled_orders'] ?></b><small>por usuario o administrador</small><i aria-hidden="true"><svg class="ico"><use href="#i-receipt"/></svg></i></article>
                <article class="stat-card"><span>Pedidos eliminados</span><b><?= (int)$stats['deleted_orders'] ?></b><small>incluye pedidos borrados con cuenta</small><i aria-hidden="true"><svg class="ico"><use href="#i-receipt"/></svg></i></article>
            </section>
            <section class="panel-card superadmin-panel" id="permissions">
                <div class="panel-title"><div><span class="eyebrow">CONTROL DE ACCESO</span><h2>Restringir operaciones</h2><p class="muted">Los permisos se validan en el servidor. Desmarca una opción para bloquear esa operación; el SuperAdmin siempre conserva el acceso de emergencia.</p></div></div>
                <form method="post" class="form-stack">
                    <?= csrfField() ?><input type="hidden" name="action" value="superadmin_permissions">
                    <div class="superadmin-permission-grid">
                        <?php foreach ($permissions as $permission): ?>
                            <label class="superadmin-permission"><input type="checkbox" name="permissions[<?= e($permission['permission_key']) ?>]" value="1" <?= (int)$permission['enabled'] === 1 ? 'checked' : '' ?>><span class="permission-switch" aria-hidden="true"></span><span><?= e($permission['label']) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <button class="button button-primary" type="submit">Guardar restricciones</button>
                </form>
            </section>
            <section class="panel-card superadmin-panel" id="superadmin-account">
                <div class="panel-title"><div><span class="eyebrow">CUENTA PROTEGIDA</span><h2>Credenciales SuperAdmin</h2><p class="muted">Puedes cambiar el usuario y la contraseña. El rol SuperAdmin no se puede modificar.</p></div></div>
                <form method="post" class="form-stack">
                    <?= csrfField() ?><input type="hidden" name="action" value="superadmin_account">
                    <label>Nombre de acceso<input required minlength="3" maxlength="80" name="username" value="<?= e($user['username']) ?>"></label>
                    <label>Nueva contraseña <span class="optional-label">Déjala vacía para conservar la actual</span><div class="password-control"><input type="password" minlength="8" name="new_password" autocomplete="new-password"><button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false"><svg class="password-eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.2 0 2.3-.3 3.3-.7"/></svg></button></div></label>
                    <button class="button button-outline" type="submit">Actualizar credenciales</button>
                </form>
            </section>
            <section class="panel-card superadmin-panel" id="media-cleanup">
                <div class="panel-title"><div><span class="eyebrow">ALMACENAMIENTO</span><h2>Archivos sin uso <span class="count-badge"><?= count($orphanedFiles) ?></span></h2><p class="muted">Solo se consideran archivos generados por GoStore que no aparecen vinculados a productos, carrusel ni mensajes. La limpieza vuelve a comprobar las referencias antes de borrar.</p></div></div>
                <?php if ($orphanedFiles): ?>
                    <ul><?php foreach ($orphanedFiles as $orphan): ?><li><?= $orphan['type'] === 'chat' ? 'Chat' : 'Producto/carrusel' ?> · <?= e($orphan['filename']) ?></li><?php endforeach; ?></ul>
                    <form method="post" data-confirm="Se eliminarán los <?= count($orphanedFiles) ?> archivos sin referencias. ¿Continuar?">
                        <?= csrfField() ?><input type="hidden" name="action" value="superadmin_cleanup_media"><button class="button button-danger" type="submit">Eliminar archivos sobrantes</button>
                    </form>
                <?php else: ?><p>No hay archivos sobrantes que limpiar.</p><?php endif; ?>
            </section>
            <section class="panel-card orders-panel superadmin-panel" id="all-orders">
                <div class="panel-title"><div><span class="eyebrow">VISIÓN GLOBAL</span><h2>Todos los pedidos <span class="count-badge"><?= count($orders) ?></span></h2></div></div>
                <?php if (!$orders): ?><p>Aún no hay pedidos registrados.</p><?php else: ?>
                    <div class="table-wrap"><table><thead><tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Pago</th><th>Total</th><th>Estado</th><th>Artículos</th></tr></thead><tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr><td><b><?= e($order['code']) ?></b><small>#<?= (int)$order['id'] ?><?= $order['deleted_at'] ? ' · retirado de la vista' : '' ?></small></td><td><?= e($order['full_name']) ?><small><?= e($order['username']) ?></small></td><td><?= e(date('d/m/Y H:i:s', strtotime($order['created_at']))) ?></td><td><?= e(str_replace('_', ' ', $order['payment_method'])) ?></td><td><?= e(formatMoney((float)$order['total'], $currency)) ?></td><td><?= e($order['status']) ?></td><td><?php foreach ($order['items'] as $item): ?><div><?= (int)$item['quantity'] ?> × <?= e($item['product_name']) ?></div><?php endforeach; ?></td></tr>
                    <?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </section>
            <section class="panel-card orders-panel superadmin-panel" id="audit-log">
                <div class="panel-title"><div><span class="eyebrow">TRAZABILIDAD</span><h2>Registro de actividad</h2><p class="muted">Incluye inicios de sesión, publicaciones, cambios de estado, cancelaciones y eliminaciones. Se muestra hora local del servidor.</p></div></div>
                <?php if (!$auditLog): ?><p>Aún no hay actividad registrada.</p><?php else: ?>
                    <div class="table-wrap"><table><thead><tr><th>Fecha y hora</th><th>Cuenta</th><th>Rol</th><th>Acción</th><th>Detalle</th></tr></thead><tbody>
                    <?php foreach ($auditLog as $event): ?><tr><td><?= e(date('d/m/Y H:i:s', strtotime($event['created_at']))) ?></td><td><?= e($event['actor_username']) ?></td><td><?= e($event['actor_role']) ?></td><td><?= e(str_replace('_', ' ', $event['action'])) ?></td><td><?= e($event['details']) ?></td></tr><?php endforeach; ?>
                    </tbody></table></div>
                    <?php if ($auditPageCount > 1): ?><nav class="form-actions" aria-label="Páginas de auditoría"><?php if ($auditPage > 1): ?><a class="button button-outline" href="index.php?page=superadmin&amp;audit_page=<?= $auditPage - 1 ?>#audit-log">Anterior</a><?php endif; ?><span>Página <?= (int)$auditPage ?> de <?= (int)$auditPageCount ?></span><?php if ($auditPage < $auditPageCount): ?><a class="button button-outline" href="index.php?page=superadmin&amp;audit_page=<?= $auditPage + 1 ?>#audit-log">Siguiente</a><?php endif; ?></nav><?php endif; ?>
                <?php endif; ?>
            </section>
            </div>
            <?php elseif ($page === 'admin'): ?>
            <section class="stats-grid">
                <article class="stat-card"><span>Usuarios registrados</span><b><?= $stats['users'] ?></b><small>cuentas de clientes</small><i><svg class="ico" aria-hidden="true"><use href="#i-user"/></svg></i></article>
                <article class="stat-card"><span>Ventas registradas</span><b><?= e(formatMoney((float)$stats['sales'], $currency)) ?></b><small>pedidos no cancelados</small><i><svg class="ico" aria-hidden="true"><use href="#i-trend"/></svg></i></article>
                <article class="stat-card"><span>Pedidos por revisar</span><b><?= $stats['orders'] ?></b><small>esperando tu aceptación</small><i><svg class="ico" aria-hidden="true"><use href="#i-clock"/></svg></i></article>
                <article class="stat-card"><span>Productos publicados</span><b><?= $stats['products'] ?></b><small>en tu catálogo</small><i><svg class="ico" aria-hidden="true"><use href="#i-grid"/></svg></i></article>
            </section>
            <section class="stats-grid admin-shortcuts">
                <?php if ($adminPageAccess['admin_products']): ?><a class="panel-card" href="index.php?page=admin-products"><b>Publicar y administrar productos</b><span>Editar catálogo y existencias →</span></a><?php endif; ?>
                <?php if ($adminPageAccess['admin_carousel']): ?><a class="panel-card" href="index.php?page=admin-carousel"><b>Administrar carrusel</b><span>Publicar imágenes →</span></a><?php endif; ?>
                <?php if ($adminPageAccess['admin_orders']): ?><a class="panel-card" href="index.php?page=admin-orders"><b>Gestionar pedidos</b><span>Revisar y actualizar estados →</span></a><?php endif; ?>
            </section>
            <?php elseif ($page === 'admin-products'): ?>
            <section class="admin-columns">
                <article class="panel-card" id="product-form">
                    <div class="panel-title"><div><span class="eyebrow"><?= $editing ? 'ACTUALIZAR CATÁLOGO' : 'NUEVO EN LA TIENDA' ?></span><h2><?= $editing ? 'Editar producto' : 'Publicar producto' ?></h2></div></div>
                    <form method="post" enctype="multipart/form-data" class="form-stack compact-form">
                        <?= csrfField() ?><input type="hidden" name="action" value="product_save"><input type="hidden" name="product_id" value="<?= (int)($editing['id'] ?? 0) ?>">
                        <label>Nombre del producto<input required name="name" value="<?= e($editing['name'] ?? '') ?>" placeholder=""></label>
                        <div class="form-row"><label>Categoría<input required name="category" list="category-list" value="<?= e($editing['category'] ?? '') ?>" placeholder=""></label><datalist id="category-list"><?php foreach ($categories as $category): ?><option value="<?= e($category) ?>"><?php endforeach; ?></datalist><label>Precio (S/)<input required type="number" name="price" min="0.01" step="0.01" value="<?= e($editing['price'] ?? '') ?>" placeholder="0.00"></label></div>
                        <label>Descripción<textarea required name="description" rows="3" placeholder=""><?= e($editing['description'] ?? '') ?></textarea></label>
                        <div class="form-row"><label>Stock disponible<input required type="number" name="stock" min="0" step="1" value="<?= e($editing['stock'] ?? '') ?>" placeholder=""></label><label>Imágenes del producto <span class="optional-label"><?= $editing ? 'Selecciona entre 2 y 4 para reemplazarlas · ' : 'Selecciona entre 2 y 4 · ' ?>JPG, PNG o WEBP · máx. 5 MB cada una</span><input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple <?= $editing ? '' : 'required' ?> data-product-images></label></div>
                        <?php if (!empty($editing['images'])): ?><div class="admin-image-previews"><?php foreach ($editing['images'] as $index => $image): ?><img class="edit-preview" src="uploads/<?= e($image) ?>" alt="Imagen <?= $index + 1 ?> actual del producto"><?php endforeach; ?></div><?php endif; ?>
                        <div class="form-actions"><?php if ($editing): ?><a class="button button-quiet" href="index.php?page=admin-products#product-form">Cancelar</a><?php endif; ?><button class="button button-primary" type="submit"><?= $editing ? 'Guardar cambios' : 'Publicar producto' ?></button></div>
                    </form>
                </article>
                <article class="panel-card products-admin">
                    <div class="panel-title"><div><span class="eyebrow">TU CATÁLOGO</span><h2>Productos <span class="count-badge"><?= count($allProducts) ?></span></h2></div></div>
                    <?php if (!$allProducts): ?><div class="empty-state"><span><svg class="ico" aria-hidden="true"><use href="#i-grid"/></svg></span><b>Aún no hay productos</b><p>Publica el primero desde el formulario.</p></div><?php else: ?>
                        <div class="admin-product-list"><?php foreach ($allProducts as $product): ?><div class="admin-product-row"><img src="<?= $product['image'] ? 'uploads/' . e($product['image']) : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=120&q=75' ?>" alt=""><div class="admin-product-info"><b><?= e($product['name']) ?></b><span><?= e($product['code']) ?> · <?= e($product['category']) ?> · <?= (int)$product['stock'] ?> en stock</span><strong><?= e(formatMoney((float)$product['price'], $currency)) ?></strong></div><a class="icon-button" href="index.php?page=admin-products&amp;edit=<?= (int)$product['id'] ?>#product-form" aria-label="Editar <?= e($product['name']) ?>">✎</a><form method="post" data-confirm="¿Eliminar este producto?"><?= csrfField() ?><input type="hidden" name="action" value="product_delete"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><button class="icon-button danger-icon" aria-label="Eliminar producto">×</button></form></div><?php endforeach; ?></div>
                    <?php endif; ?>
                </article>
            </section>
            <?php elseif ($page === 'admin-carousel'): ?>
            <section class="panel-card carousel-admin" id="carousel-form">
                <div class="panel-title"><div><span class="eyebrow">DESTACADOS DE LA TIENDA</span><h2>Administrar carrusel</h2>                <p class="muted">Publica imágenes y el carrusel las mostrará automáticamente junto con productos destacados.</p></div></div>
                <form method="post" enctype="multipart/form-data" class="form-stack carousel-upload-form">
                    <?= csrfField() ?><input type="hidden" name="action" value="carousel_save">
                    <label>Imagen para el carrusel <span class="optional-label">JPG, PNG o WEBP · máx. 5 MB</span><input required type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
                    <div class="form-actions"><button class="button button-primary" type="submit">Agregar al carrusel</button></div>
                </form>
                <?php if ($carouselImages): ?><div class="carousel-admin-list"><?php foreach ($carouselImages as $carouselImage): ?><div class="carousel-admin-item"><img src="uploads/<?= e($carouselImage['image']) ?>" alt="<?= e($carouselImage['caption'] ?: 'Imagen del carrusel') ?>"><div><b><?= e($carouselImage['caption'] ?: 'Imagen sin texto') ?></b><small>Imagen publicada en el carrusel</small></div><form method="post" data-confirm="¿Retirar esta imagen del carrusel?"><?= csrfField() ?><input type="hidden" name="action" value="carousel_delete"><input type="hidden" name="carousel_id" value="<?= (int)$carouselImage['id'] ?>"><button class="icon-button danger-icon" type="submit" aria-label="Retirar imagen">×</button></form></div><?php endforeach; ?></div><?php endif; ?>
            </section>
            <?php elseif ($page === 'admin-users'): ?>
            <section class="panel-card orders-panel" id="users">
                <div class="panel-title"><div><span class="eyebrow">CUENTAS DE CLIENTES</span><h2>Usuarios registrados <span class="count-badge"><?= count($users) ?></span></h2><p class="muted">Eliminar una cuenta también borra sus pedidos, reseñas y notificaciones. Esta acción es permanente.</p></div></div>
                <?php if (!$users): ?><div class="empty-state compact-empty"><span><svg class="ico" aria-hidden="true"><use href="#i-user"/></svg></span><b>No hay usuarios registrados</b></div><?php else: ?>
                    <div class="table-wrap"><table><thead><tr><th>Usuario</th><th>Nombre completo</th><th>Correo electrónico</th><th>Registro</th><th>Acción</th></tr></thead><tbody>
                    <?php foreach ($users as $account): ?>
                        <tr>
                            <td><b><?= e($account['username']) ?></b></td>
                            <td><?= e($account['full_name']) ?></td>
                            <td><a href="mailto:<?= e($account['email']) ?>"><?= e($account['email']) ?></a></td>
                            <td><?= e(date('d/m/Y', strtotime($account['created_at']))) ?></td>
                            <td><form method="post" data-confirm="Eliminar permanentemente esta cuenta y todos sus pedidos, reseñas y notificaciones. Esta acción no se puede deshacer."><?= csrfField() ?><input type="hidden" name="action" value="admin_user_delete"><input type="hidden" name="user_id" value="<?= (int)$account['id'] ?>"><button class="button button-small button-danger" type="submit">Eliminar cuenta</button></form></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </section>
            <?php else: ?>
            <section class="panel-card orders-panel" id="orders">
                <div class="panel-title"><div><span class="eyebrow">SEGUIMIENTO DE COMPRAS</span><h2>Pedidos de clientes</h2></div></div>
                <?php if (!$orders): ?><div class="empty-state compact-empty"><span><svg class="ico" aria-hidden="true"><use href="#i-receipt"/></svg></span><b>No hay pedidos en esta vista</b><p>Los pedidos retirados se conservan y aparecen en las exportaciones.</p></div><?php else: ?>
                    <div class="table-wrap"><table><thead><tr><th>Pedido</th><th>Cliente / correo</th><th>Fecha</th><th>Pago</th><th>Total</th><th>Estado / acción</th></tr></thead><tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><b class="order-code"><?= e($order['code']) ?></b><small>#<?= (int)$order['id'] ?></small><details><summary>Ver productos</summary><?php foreach ($order['items'] as $item): ?><div><?= (int)$item['quantity'] ?> × <?= e($item['product_name']) ?> · <?= e($item['product_code']) ?></div><?php endforeach; ?><div><?= e($order['address']) ?></div></details></td>
                            <td><?= e($order['full_name']) ?><small><a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a></small><small><?= e($order['phone']) ?></small></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                            <td><span class="payment-tag"><?= e(str_replace('_', ' ', $order['payment_method'])) ?></span></td>
                            <td><b><?= e(formatMoney((float)$order['total'], $currency)) ?></b></td>
                            <td>
                                <span class="status-badge status-<?= e(strtolower(str_replace(' ', '-', $order['status']))) ?>"><i class="dot" aria-hidden="true"></i><?= e($order['status']) ?></span>
                                <?php if (!in_array($order['status'], ['Entregado', 'Cancelado'], true)): ?>
                                    <form method="post" class="status-form"><?= csrfField() ?><input type="hidden" name="action" value="order_status"><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><select name="status" aria-label="Actualizar estado"><option value="">Actualizar…</option><?php foreach (['Aceptado', 'En camino', 'Entregado', 'Cancelado'] as $status): ?><option value="<?= e($status) ?>"><?= e($status) ?></option><?php endforeach; ?></select><button class="button button-small button-outline">Guardar</button></form>
                                <?php endif; ?>
                                <form method="post" class="status-form" data-confirm="El pedido se quitará de esta vista, pero sus datos seguirán guardados y aparecerán en los reportes con la fecha de retiro."><?= csrfField() ?><input type="hidden" name="action" value="order_archive"><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><button class="button button-small button-danger" type="submit">Quitar de vista</button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

        <?php elseif ($page === 'store' && isAdmin()): ?>
            <section class="catalog-section" id="productos">
                <div class="section-heading catalog-heading">
                    <div><span class="eyebrow">CATÁLOGO</span><h1><?= $search !== '' ? 'Resultados de búsqueda' : ($categoryFilter !== '' ? e($categoryFilter) : 'Productos disponibles') ?></h1><p><?= $matchingProducts ?> producto<?= $matchingProducts === 1 ? '' : 's' ?> para consultar.</p></div>
                    <?php if ($search !== '' || $categoryFilter !== ''): ?><a class="text-button" href="<?= isAdmin() ? 'index.php?page=store' : 'index.php' ?>">Limpiar filtros ×</a><?php endif; ?>
                </div>
                <?php if (!$products): ?>
                    <section class="panel-card empty-state"><span><svg class="ico" aria-hidden="true"><use href="#i-grid"/></svg></span><b>Aún no hay productos publicados</b><p>Los productos que se publiquen aparecerán aquí.</p></section>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <?php if ((int)$product['search_match'] !== 1 || ($categoryFilter !== '' && $product['category'] !== $categoryFilter)) continue; ?>
                            <article class="product-card">
                                <div class="product-image"><?php if ($product['image']): ?><img src="uploads/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>"><?php else: ?><div class="image-placeholder">✦</div><?php endif; ?></div>
                                <div class="product-card-body"><span class="product-category"><?= e($product['category']) ?></span><b class="product-name"><?= e($product['name']) ?></b><div class="product-card-bottom"><strong><?= e(formatMoney((float)$product['price'], $currency)) ?></strong><span><?= (int)$product['stock'] > 0 ? (int)$product['stock'] . ' disponibles' : 'Agotado' ?></span></div></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        <?php elseif ($page === 'profile'): ?>
            <section class="page-heading"><span class="eyebrow">TU CUENTA</span><h1>Mi perfil</h1><p>Mantén tus datos actualizados para recibir tus pedidos.</p></section>
            <div class="profile-layout">
                <section class="panel-card profile-card"><form method="post" class="form-stack">
                    <?= csrfField() ?><input type="hidden" name="action" value="profile">
                    <div class="form-row"><label>Nombre completo<input required name="full_name" value="<?= e($user['full_name']) ?>"></label><label>Nombre de usuario<input disabled value="<?= e($user['username']) ?>"></label></div>
                    <label>Correo electrónico<input required type="email" name="email" maxlength="190" value="<?= e($user['email']) ?>"></label>
                    <div class="form-row"><label><?= isAdmin() ? 'Teléfono / WhatsApp del administrador' : 'Teléfono' ?><input type="tel" name="phone" value="<?= e($user['phone']) ?>" autocomplete="tel" placeholder="<?= isAdmin() ? '+51 999 999 999' : '' ?>"></label><label>Dirección de entrega<input name="address" value="<?= e($user['address']) ?>" placeholder=""></label></div>
                    <label>Nueva contraseña <span class="optional-label">Déjala vacía para conservar la actual</span><div class="password-control"><input type="password" name="new_password" minlength="8" autocomplete="new-password"><button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false"><svg class="password-eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.2 0 2.3-.3 3.3-.7"/></svg></button></div></label>
                    <div class="form-actions"><button class="button button-primary" type="submit">Guardar cambios</button></div>
                </form></section>
                <?php if (!isAdmin()): ?>
                    <section class="panel-card account-delete-card">
                        <div><span class="eyebrow">ZONA DE PELIGRO</span><h2>Eliminar mi cuenta</h2><p>Se borrarán permanentemente tu cuenta, tus pedidos y tus reseñas. Esta acción no se puede deshacer.</p></div>
                        <form method="post" class="form-stack" data-confirm="Eliminar tu cuenta y todos tus datos y pedidos de forma permanente. Esta acción no se puede deshacer.">
                            <?= csrfField() ?><input type="hidden" name="action" value="account_delete">
                            <label>Confirma tu contraseña actual<div class="password-control"><input required type="password" name="current_password" autocomplete="current-password"><button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false"><svg class="password-eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.2 0 2.3-.3 3.3-.7"/></svg></button></div></label>
                            <div class="form-actions"><button class="button button-danger" type="submit">Eliminar mi cuenta</button></div>
                        </form>
                    </section>
                <?php endif; ?>
            </div>

        <?php elseif ($page === 'settings'): ?>
            <section class="page-heading"><span class="eyebrow">PREFERENCIAS</span><h1>Ajustes</h1><p>Personaliza el idioma, la moneda y los colores de tu página.</p></section>
            <section class="panel-card profile-card"><form method="post" class="form-stack">
                <?= csrfField() ?><input type="hidden" name="action" value="settings">
                <div class="form-row">
                    <label>Idioma
                        <select name="language">
                            <option value="es" <?= ($language === 'es' ? 'selected' : '') ?>>Español</option>
                            <option value="en" <?= ($language === 'en' ? 'selected' : '') ?>>English</option>
                            <option value="fr" <?= ($language === 'fr' ? 'selected' : '') ?>>Français</option>
                            <option value="pt" <?= ($language === 'pt' ? 'selected' : '') ?>>Português</option>
                            <option value="it" <?= ($language === 'it' ? 'selected' : '') ?>>Italiano</option>
                        </select>
                    </label>
                    <label>Moneda
                        <select name="currency">
                            <option value="PEN" <?= ($currency === 'PEN' ? 'selected' : '') ?>>Sol peruano (S/)</option>
                            <option value="USD" <?= ($currency === 'USD' ? 'selected' : '') ?>>Dólar estadounidense ($)</option>
                            <option value="EUR" <?= ($currency === 'EUR' ? 'selected' : '') ?>>Euro (€)</option>
                        </select>
                        <span class="optional-label">Conversión fija: 1 USD = S/ 3.70 · 1 EUR = S/ 4.00</span>
                    </label>
                    <div class="theme-picker">
                        <span class="theme-picker-title">Estilo de color</span>
                        <div class="theme-options">
                            <label class="theme-card <?= $theme === 'violeta' ? 'is-selected' : '' ?>"><input type="radio" name="theme" value="violeta" <?= ($theme === 'violeta' ? 'checked' : '') ?>><span class="theme-swatch theme-swatch-violeta"></span><span>Violeta</span></label>
                            <label class="theme-card <?= $theme === 'naranja' ? 'is-selected' : '' ?>"><input type="radio" name="theme" value="naranja" <?= ($theme === 'naranja' ? 'checked' : '') ?>><span class="theme-swatch theme-swatch-naranja"></span><span>Naranja</span></label>
                            <label class="theme-card <?= $theme === 'verde' ? 'is-selected' : '' ?>"><input type="radio" name="theme" value="verde" <?= ($theme === 'verde' ? 'checked' : '') ?>><span class="theme-swatch theme-swatch-verde"></span><span>Verde</span></label>
                            <label class="theme-card <?= $theme === 'azul' ? 'is-selected' : '' ?>"><input type="radio" name="theme" value="azul" <?= ($theme === 'azul' ? 'checked' : '') ?>><span class="theme-swatch theme-swatch-azul"></span><span>Azul</span></label>
                            <label class="theme-card <?= $theme === 'oscuro' ? 'is-selected' : '' ?>"><input type="radio" name="theme" value="oscuro" <?= ($theme === 'oscuro' ? 'checked' : '') ?>><span class="theme-swatch theme-swatch-oscuro"></span><span>Oscuro</span></label>
                        </div>
                    </div>
                </div>
                <div class="form-actions"><button class="button button-primary" type="submit">Guardar ajustes</button></div>
            </form></section>

        <?php elseif ($page === 'orders'): ?>
            <section class="page-heading"><span class="eyebrow">TU HISTORIAL</span><h1>Mis pedidos</h1><p>Sigue cada paso hasta que tu compra llegue a casa.</p></section>
            <?php if (!$myOrders): ?><section class="panel-card empty-state"><span><svg class="ico" aria-hidden="true"><use href="#i-receipt"/></svg></span><b>Aún no tienes pedidos</b><p>Cuando realices tu primera compra, podrás seguirla aquí.</p><a class="button button-primary" href="index.php">Explorar productos</a></section><?php endif; ?>
            <div class="order-list"><?php foreach ($myOrders as $order): ?>
                <article class="order-card"><div class="order-card-head"><div><span class="eyebrow">TU CÓDIGO DE PEDIDO</span><strong class="order-code customer-order-code"><?= e($order['code']) ?></strong><span class="muted order-reference">Pedido #<?= (int)$order['id'] ?> · <?= e(date('d/m/Y · H:i', strtotime($order['created_at']))) ?> · <?= e(strtoupper(str_replace('_', ' ', $order['payment_method']))) ?></span></div><h2><?= e(formatMoney((float)$order['total'], $currency)) ?></h2><?php $statusEmojis = ['Pendiente' => '⏳', 'Aceptado' => '✅', 'En camino' => '🚚', 'Entregado' => '📦', 'Cancelado' => '❌']; ?><span class="status-badge status-<?= e(strtolower(str_replace(' ', '-', $order['status']))) ?>"><i class="dot" aria-hidden="true"></i><?= e($order['status']) ?></span></div>
                <div class="timeline <?= $order['status'] === 'Cancelado' ? 'timeline-cancelled' : '' ?>">
                    <?php $steps = ['Pendiente', 'Aceptado', 'En camino', 'Entregado']; $statusEmojis = ['Pendiente' => '⏳', 'Aceptado' => '✅', 'En camino' => '🚚', 'Entregado' => '📦']; $current = array_search($order['status'], $steps, true); foreach ($steps as $index => $step): ?><div class="timeline-step <?= $current !== false && $index <= $current ? 'done' : '' ?>"><span class="timeline-dot" aria-hidden="true"><?= $current !== false && $index <= $current ? '<svg class="ico" aria-hidden="true"><use href="#i-check"/></svg>' : $index + 1 ?></span><b><?= e($step) ?></b><?php if ($index === 1): ?><small>El admin revisará el pedido</small><?php elseif ($index === 2): ?><small>Tu compra va en camino</small><?php endif; ?></div><?php endforeach; ?>
                </div>
                <?php foreach ($order['items'] as $item): ?><div class="order-line"><span><?= (int)$item['quantity'] ?> × <?= e($item['product_name']) ?><small class="product-code-label">Código <?= e($item['product_code']) ?></small></span><span><?= e(formatMoney((float)$item['unit_price'] * (int)$item['quantity'], $currency)) ?></span></div><?php endforeach; ?>
                <div class="delivery-estimate">⌖ &nbsp; Tiempo estimado de entrega: <b>1 a 3 días hábiles</b>. El administrador actualizará el seguimiento.</div>
                <?php if ($order['status'] === 'Pendiente'): ?>
                    <div class="customer-order-actions">
                        <form method="post" data-confirm="¿Quieres cancelar este pedido? Quedará marcado como cancelado y los productos volverán al stock.">
                            <?= csrfField() ?><input type="hidden" name="action" value="customer_order_cancel"><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                            <button class="button button-quiet" type="submit">Cancelar pedido</button>
                        </form>
                        <form method="post" data-confirm="Esta acción eliminará permanentemente el pedido y sus detalles. No se puede deshacer.">
                            <?= csrfField() ?><input type="hidden" name="action" value="customer_order_delete"><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                            <button class="button button-danger" type="submit">Eliminar pedido</button>
                        </form>
                    </div>
                <?php elseif (in_array($order['status'], ['Cancelado', 'Entregado'], true)): ?>
                    <div class="customer-order-actions">
                        <form method="post" data-confirm="Esta acción eliminará permanentemente el pedido y sus detalles de tu historial. No se puede deshacer.">
                            <?= csrfField() ?><input type="hidden" name="action" value="customer_order_delete"><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                            <button class="button button-danger" type="submit">Eliminar pedido</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="customer-order-help">Este pedido ya se está preparando o está en camino. Para cancelarlo, contacta al administrador.</p>
                <?php endif; ?>
                <?php
                    $receiptLines = ['BOLETA DE COMPRA · GOSTORE', 'Código único de compra: ' . $order['code'], 'Cliente: ' . $user['full_name'], 'Fecha: ' . date('d/m/Y H:i', strtotime($order['created_at'])), 'Pago: ' . strtoupper(str_replace('_', ' ', $order['payment_method'])), 'Estado: ' . $order['status'], 'Productos:'];
                    foreach ($order['items'] as $item) {
                        $receiptLines[] = (int)$item['quantity'] . ' x ' . $item['product_name'] . ' · Código: ' . $item['product_code'] . ' · S/ ' . number_format((float)$item['unit_price'] * (int)$item['quantity'], 2);
                    }
                    $receiptLines[] = 'Total: S/ ' . number_format((float)$order['total'], 2);
                    $receiptText = implode("\n", $receiptLines);
                    $whatsappReceiptUrl = 'https://wa.me/' . $adminPhone . '?text=' . rawurlencode($receiptText);
                    $gmailReceiptUrl = 'https://mail.google.com/mail/?view=cm&fs=1&to=' . rawurlencode($adminEmail) . '&su=' . rawurlencode('Boleta de compra ' . $order['code']) . '&body=' . rawurlencode($receiptText);
                ?>
                <div class="receipt-share-actions" aria-label="Compartir boleta">
                    <a class="button button-outline receipt-share-button" href="<?= e($whatsappReceiptUrl) ?>" target="_blank" rel="noopener">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20.52 3.48A11.82 11.82 0 0 0 12.08 0C5.48 0 .1 5.38.1 12c0 2.12.55 4.18 1.6 6L.04 24l6.14-1.61A11.9 11.9 0 0 0 12.07 24C18.67 24 24 18.62 24 12c0-3.2-1.24-6.2-3.48-8.52ZM12.08 21.82a9.8 9.8 0 0 1-5-1.37l-.36-.21-3.64.95.97-3.55-.23-.37A9.78 9.78 0 1 1 12.08 21.82Zm5.37-7.33c-.29-.15-1.74-.86-2.01-.96-.27-.1-.47-.15-.67.15-.2.29-.77.96-.94 1.16-.17.2-.35.22-.64.07-.29-.15-1.23-.45-2.35-1.45-.87-.77-1.45-1.73-1.62-2.02-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.2-.29.29-.49.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.91-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.29-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.2.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.74-.71 1.99-1.4.25-.68.17-1.27.17-1.39-.07-.12-.27-.2-.56-.34Z"/></svg>
                        Enviar por WhatsApp
                    </a>
                    <a class="button button-outline receipt-share-button" href="<?= e($gmailReceiptUrl) ?>" target="_blank" rel="noopener">
                        <svg class="google-logo" aria-hidden="true" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 3.04 13.22l7.98 6.19C12.91 13.72 18.01 9.5 24 9.5Z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.93c-.58 2.96-2.26 5.48-4.75 7.18l7.73 6C44.42 38.08 47 31.91 47 24.55Z"/><path fill="#FBBC05" d="M11.02 28.59A14.5 14.5 0 0 1 10.25 24c0-1.59.28-3.13.77-4.59l-7.98-6.19A23.9 23.9 0 0 0 .5 24c0 3.87.93 7.53 2.54 10.78l7.98-6.19Z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.14 1.44-4.88 2.3-8.18 2.3-5.99 0-11.09-4.22-12.98-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/></svg>
                        Enviar por Gmail
                    </a>
                </div>
                </article>
            <?php endforeach; ?></div>

        <?php elseif ($page === 'favorites'): ?>
            <section class="page-heading"><span class="eyebrow">TU COLECCIÓN</span><h1>Mis favoritos</h1><p>Productos que guardaste para volver a encontrarlos fácilmente.</p></section>
            <?php if (!$products): ?>
                <section class="panel-card empty-state"><span><svg class="ico" aria-hidden="true"><use href="#i-heart"/></svg></span><b>Aún no tienes productos favoritos</b><p>Guarda los productos que te interesan y aparecerán aquí.</p><a class="button button-primary" href="index.php">Explorar productos</a></section>
            <?php else: ?>
                <div class="product-grid favorite-product-grid">
                    <?php foreach ($products as $product): ?>
                        <?php require __DIR__ . '/partials/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php elseif ($page === 'cart'): ?>
            <section class="page-heading"><span class="eyebrow">CASI TUYO</span><h1>Tu carrito</h1><p>Revisa tus productos y elige cómo quieres pagar.</p></section>
            <?php if (!$cartItems): ?><section class="panel-card empty-state"><span><svg class="ico" aria-hidden="true"><use href="#i-cart"/></svg></span><b>Tu carrito está esperando algo bueno</b><p>Explora el catálogo y agrega tus favoritos.</p><a class="button button-primary" href="index.php">Seguir comprando</a></section><?php else: ?>
                <div class="checkout-layout"><section class="panel-card cart-panel"><form method="post"><?= csrfField() ?><input type="hidden" name="action" value="cart_update"><div class="cart-title"><h2>Productos <span class="count-badge"><?= count($cartItems) ?></span></h2><button class="text-button">Actualizar carrito</button></div>
                    <?php foreach ($cartItems as $product): ?><div class="cart-item"><img src="<?= $product['image'] ? 'uploads/' . e($product['image']) : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=180&q=80' ?>" alt="<?= e($product['name']) ?>"><div class="cart-item-info"><span><?= e($product['category']) ?></span><b><?= e($product['name']) ?></b><small>Stock: <?= (int)$product['stock'] ?></small><label class="quantity-input">Cant. <input type="number" name="quantity[<?= (int)$product['id'] ?>]" min="0" max="<?= (int)$product['stock'] ?>" value="<?= (int)$product['quantity'] ?>"></label></div><strong><?= e(formatMoney((float)$product['line_total'], $currency)) ?></strong></div><?php endforeach; ?>
                    <div class="cart-update-mobile"><button class="button button-quiet">Actualizar cantidades</button></div></form>
                    </section><section class="panel-card checkout-card"><span class="eyebrow">RESUMEN DE COMPRA</span><h2>Finaliza tu pedido</h2><div class="summary-line"><span>Subtotal</span><b><?= e(formatMoney((float)$cartTotal, $currency)) ?></b></div><div class="summary-line"><span>Envío</span><b class="free-shipping">A coordinar</b></div><div class="summary-total"><span>Total</span><b><?= e(formatMoney((float)$cartTotal, $currency)) ?></b></div>
                    <?php if ($user): ?>
                        <form method="post" class="form-stack checkout-form"><?= csrfField() ?><input type="hidden" name="action" value="checkout">
                            <label>Medio de pago</label>
                            <div class="payment-options">
                                <label class="payment-option"><input type="radio" name="payment_method" value="yape" required><span class="payment-logo yape-logo">Y</span><span>Yape</span></label>
                                <label class="payment-option"><input type="radio" name="payment_method" value="plin"><span class="payment-logo plin-logo">P</span><span>Plin</span></label>
                                <label class="payment-option"><input type="radio" name="payment_method" value="bbva"><span class="payment-logo bbva-logo">BBVA</span><span>BBVA</span></label>
                                <label class="payment-option"><input type="radio" name="payment_method" value="contra_entrega"><span class="payment-logo cash-logo">S/</span><span>Contra entrega</span></label>
                            </div>
                            <div class="payment-instructions" id="payment-instructions" hidden></div>
                            <div id="receipt-template" hidden><div class="receipt-heading">BOLETA DE COMPRA · GOSTORE</div><?php foreach ($cartItems as $item): ?><div class="receipt-line"><?= (int)$item['quantity'] ?> × <?= e($item['name']) ?> · <?= e($item['code']) ?><span><?= e(formatMoney((float)$item['line_total'], $currency)) ?></span></div><?php endforeach; ?><div class="receipt-total">TOTAL <b><?= e(formatMoney((float)$cartTotal, $currency)) ?></b></div></div>
                            <label>Dirección y referencia<textarea required name="delivery_note" rows="2" placeholder="<?= e($user['address'] ?: 'Completa la dirección en tu perfil o déjanos una referencia') ?>"><?= e($user['address']) ?></textarea></label>
                            <button class="button button-primary button-wide" type="submit" data-formatted-total="<?= e(formatMoney((float)$cartTotal, $currency)) ?>">Confirmar pedido · <?= e(formatMoney((float)$cartTotal, $currency)) ?> <span>→</span></button>
                            <small class="secure-note">⌑ &nbsp; Tu pedido será revisado por el administrador.</small>
                        </form>
                    <?php else: ?><div class="login-checkout"><p>Inicia sesión para completar tu compra.</p><a class="button button-primary button-wide" href="index.php?page=login">Iniciar sesión</a><small>¿Nuevo en GoStore? <a href="index.php?page=register">Crear cuenta</a></small></div><?php endif; ?>
                </section></div>
            <?php endif; ?>

        <?php else: ?>
            <section class="welcome" data-catalog-promo <?= $search !== '' || $categoryFilter !== '' ? 'hidden' : '' ?>>
                <div class="welcome-copy"><h1>Todo lo que necesitas, en un solo lugar</h1><p>Productos seleccionados, pagos con Yape, Plin o BBVA y seguimiento de tu pedido paso a paso.</p><div class="welcome-actions"><a class="button button-primary" href="#productos">Ver productos <span aria-hidden="true">→</span></a><?php if (!$user): ?><a class="button button-outline" href="index.php?page=register">Crear cuenta</a><?php endif; ?></div></div>
                <ul class="welcome-points"><li><svg class="ico" aria-hidden="true"><use href="#i-truck"/></svg><span><b>Pedidos con seguimiento</b><small>Del pedido a tu puerta</small></span></li><li><svg class="ico" aria-hidden="true"><use href="#i-trend"/></svg><span><b>Pago a tu manera</b><small>Yape, Plin, BBVA o contra entrega</small></span></li><li><svg class="ico" aria-hidden="true"><use href="#i-shield"/></svg><span><b>Compra con confianza</b><small>Atención por WhatsApp</small></span></li></ul>
            </section>
            <?php if ($search === '' && $categoryFilter === '' && !$selectedProduct): ?>
            <?php
            $showPresentation = $user && empty($_SESSION['presentation_shown']) && empty($showUserGuide);
            if ($showPresentation) {
                $_SESSION['presentation_shown'] = true;
            }
            ?>
            <div class="presentation-overlay" id="featured-carousel" data-presentation-key="<?= $user ? '' : 'gostore-presentation-guest' ?>" data-presentation-per-login="<?= $user ? 'true' : 'false' ?>" role="dialog" aria-modal="true" aria-labelledby="presentation-title" aria-hidden="<?= $user && $showPresentation ? 'false' : 'true' ?>" <?= $user && $showPresentation ? '' : 'hidden' ?>>
                <section class="presentation-dialog store-carousel">
                    <button class="presentation-close" type="button" aria-label="Cerrar presentación">×</button>
                    <div class="section-heading"><div><span class="eyebrow">BIENVENIDO A GOSTORE</span><h2 id="presentation-title">Encuentra tus próximos favoritos</h2><p>Explora productos destacados, recomendaciones y novedades.</p></div><?php if ($carouselSlideCount > 1): ?><div class="carousel-controls"><button class="carousel-control" data-scroll="-1" aria-label="Anterior">←</button><button class="carousel-control" data-scroll="1" aria-label="Siguiente">→</button></div><?php endif; ?></div>
                <div class="carousel-viewport">
                    <div class="carousel-track" id="featured-track">
                        <?php foreach ($carouselImages as $carouselImage): ?><a class="featured-card featured-banner-card" href="#productos"><div class="featured-image"><img src="uploads/<?= e($carouselImage['image']) ?>" alt="<?= e($carouselImage['caption'] ?: 'Imagen destacada de GoStore') ?>" loading="lazy" decoding="async"></div><div class="featured-info"><span>DESTACADO</span><b><?= e($carouselImage['caption'] ?: 'Descubre nuestras novedades') ?></b></div></a><?php endforeach; ?>
                        <?php foreach ($featured as $product): ?><a class="featured-card" href="index.php?product=<?= (int)$product['id'] ?>"><div class="featured-image"><?php if ($product['image']): ?><img src="uploads/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy" decoding="async"><?php else: ?><div class="image-placeholder">✦</div><?php endif; ?><span class="rating-chip">★ <?= number_format((float)$product['avg_rating'], 1) ?></span></div><div class="featured-info"><span><?= e($product['category']) ?> · <?= (int)$product['review_count'] ?> reseñas</span><b><?= e($product['name']) ?></b><strong><?= e(formatMoney((float)$product['price'], $currency)) ?></strong></div></a><?php endforeach; ?>
                        <?php foreach ($featuredReviews as $review): ?><a class="featured-card featured-review-card" href="index.php?product=<?= (int)$review['product_id'] ?>#reviews"><div class="featured-image"><?php if ($review['product_image']): ?><img src="uploads/<?= e($review['product_image']) ?>" alt="<?= e($review['product_name']) ?>" loading="lazy" decoding="async"><?php else: ?><div class="image-placeholder">✦</div><?php endif; ?><span class="rating-chip">♥ <?= (int)$review['likes'] ?></span></div><div class="featured-info"><span><?= str_repeat('★', (int)$review['rating']) ?> · <?= e($review['username']) ?></span><b><?= e($review['product_name']) ?></b><p><?= e($review['comment']) ?></p></div></a><?php endforeach; ?>
                        <?php if ($carouselSlideCount === 0): ?><div class="carousel-empty"><b>Pronto habrá productos destacados</b><span>El administrador puede publicar imágenes desde el panel.</span></div><?php endif; ?>
                    </div>
                </div>
                <?php if ($carouselSlideCount > 1): ?><div class="carousel-indicators" id="carousel-indicators" aria-label="Diapositivas del carrusel"></div><?php endif; ?>
                </section>
            </div>
            <?php endif; ?>
            <section class="catalog-section" id="productos">
                <div class="section-heading catalog-heading"><div><span class="eyebrow">HECHO PARA TI</span><h2 id="catalog-title"><?= $search ? 'Resultados de búsqueda' : ($categoryFilter ? e($categoryFilter) : 'Explora todos los productos') ?></h2><p id="catalog-result-count" aria-live="polite"><?= $matchingProducts ?> producto<?= $matchingProducts === 1 ? '' : 's' ?> para descubrir.</p></div><a class="text-button" id="clear-filters" href="index.php" <?= $search || $categoryFilter ? '' : 'hidden' ?>>Limpiar filtros ×</a></div>
                <?php if ($categories): ?><nav class="category-chips" aria-label="Filtrar por categoría"><a class="chip <?= $categoryFilter === '' ? 'active' : '' ?>" href="index.php" data-category-filter="">Todo</a><?php foreach ($categories as $category): ?><a class="chip <?= $categoryFilter === $category ? 'active' : '' ?>" href="index.php?category=<?= rawurlencode($category) ?>" data-category-filter="<?= e($category) ?>"><?= e($category) ?></a><?php endforeach; ?></nav><?php endif; ?>
                <?php if ($selectedProduct): ?>
                    <div class="product-modal-overlay" id="product-modal" role="dialog" aria-modal="true" aria-labelledby="product-title">
                        <div class="product-modal" id="product-modal-content" tabindex="-1">
                            <a class="product-modal-close" href="index.php#productos" aria-label="Cerrar detalle del producto">×</a>
                            <div class="product-modal-heading"><span>GOSTORE <i></i> DETALLE DEL PRODUCTO</span><small>Información y opiniones</small></div>
                            <article class="product-detail product-modal-product panel-card">
                                <?php if (!empty($selectedProduct['images'])): ?>
                                    <div class="detail-gallery" data-product-gallery>
                                        <div class="detail-image">
                                            <?php foreach ($selectedProduct['images'] as $index => $image): ?><img class="product-gallery-image" src="uploads/<?= e($image) ?>" alt="<?= e($selectedProduct['name']) ?> · imagen <?= $index + 1 ?>" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>" decoding="async" <?= $index === 0 ? '' : 'hidden' ?> data-product-gallery-slide>
                                            <?php endforeach; ?>
                                            <?php if (count($selectedProduct['images']) > 1): ?><button class="product-gallery-control previous" type="button" data-gallery-step="-1" aria-label="Imagen anterior">‹</button><button class="product-gallery-control next" type="button" data-gallery-step="1" aria-label="Imagen siguiente">›</button><?php endif; ?>
                                        </div>
                                        <?php if (count($selectedProduct['images']) > 1): ?><div class="product-gallery-indicators" aria-label="Imágenes del producto">
                                            <?php foreach ($selectedProduct['images'] as $index => $image): ?><button type="button" class="product-gallery-indicator <?= $index === 0 ? 'active' : '' ?>" data-gallery-index="<?= $index ?>" aria-label="Mostrar imagen <?= $index + 1 ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button><?php endforeach; ?>
                                        </div><?php endif; ?>
                                    </div>
                                <?php else: ?><div class="detail-image"><div class="image-placeholder">✦</div></div><?php endif; ?>
                                <div class="detail-info">
                                    <span class="eyebrow"><?= e($selectedProduct['category']) ?></span>
                                    <h1 id="product-title"><?= e($selectedProduct['name']) ?></h1>
                                    <div class="detail-rating"><span><?= str_repeat('★', (int)round((float)$selectedProduct['avg_rating'])) ?><?= str_repeat('☆', 5 - (int)round((float)$selectedProduct['avg_rating'])) ?></span> <?= number_format((float)$selectedProduct['avg_rating'], 1) ?> <i></i> <?= (int)$selectedProduct['review_count'] ?> reseñas</div>
                                    <p><?= nl2br(e($selectedProduct['description'])) ?></p>
                                    <div class="detail-stock"><?= (int)$selectedProduct['stock'] > 0 ? '✓ Disponible · ' . (int)$selectedProduct['stock'] . ' en stock' : 'Agotado' ?></div>
                                    <div class="detail-buy">
                                        <strong><?= e(formatMoney((float)$selectedProduct['price'], $currency)) ?></strong>
                                        <form method="post">
                                            <?= csrfField() ?><input type="hidden" name="action" value="cart_add"><input type="hidden" name="product_id" value="<?= (int)$selectedProduct['id'] ?>">
                                            <input type="number" name="quantity" min="1" max="<?= max(1, (int)$selectedProduct['stock']) ?>" value="1" aria-label="Cantidad" <?= (int)$selectedProduct['stock'] < 1 ? 'disabled' : '' ?>>
                                            <button class="button button-primary" <?= (int)$selectedProduct['stock'] < 1 ? 'disabled' : '' ?>>Agregar al carrito <span aria-hidden="true">→</span></button>
                                        </form>
                                    </div>
                                    <?php $product = $selectedProduct; $favoriteControlCompact = false; require __DIR__ . '/partials/favorite-control.php'; unset($favoriteControlCompact, $product); ?>
                                </div>
                            </article>
                            <?php
                                $comparisonCandidates = array_filter(
                                    $products,
                                    static fn(array $candidate): bool => (int)$candidate['id'] !== (int)$selectedProduct['id']
                                        && $candidate['category'] === $selectedProduct['category']
                                );
                            ?>
                            <section class="product-comparison panel-card" id="product-comparison">
                                <div class="product-comparison-heading"><span class="eyebrow">COMPARA ANTES DE ELEGIR</span><h2>Comparar productos</h2><p>Elige otro producto de la categoría <?= e($selectedProduct['category']) ?> para revisar sus diferencias.</p></div>
                                <?php if ($comparisonCandidates): ?>
                                    <form method="get" action="index.php#product-comparison" class="product-comparison-form">
                                        <input type="hidden" name="product" value="<?= (int)$selectedProduct['id'] ?>">
                                        <label for="compare-product">Producto para comparar
                                            <select id="compare-product" name="compare" required>
                                                <option value="">Selecciona un producto</option>
                                                <?php foreach ($comparisonCandidates as $candidate): ?>
                                                    <option value="<?= (int)$candidate['id'] ?>" <?= $comparisonProduct && (int)$comparisonProduct['id'] === (int)$candidate['id'] ? 'selected' : '' ?>><?= e($candidate['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <button class="button button-primary" type="submit">Comparar</button>
                                    </form>
                                <?php else: ?>
                                    <p class="product-comparison-empty">No hay otro producto disponible en esta categoría para comparar.</p>
                                <?php endif; ?>
                                <?php if ($comparisonProduct): ?>
                                    <div class="table-wrap comparison-table-wrap">
                                        <table class="comparison-table">
                                            <thead><tr><th scope="col">Característica</th><th scope="col"><?= e($selectedProduct['name']) ?></th><th scope="col"><?= e($comparisonProduct['name']) ?></th></tr></thead>
                                            <tbody>
                                                <tr><th scope="row">Categoría</th><td><?= e($selectedProduct['category']) ?></td><td><?= e($comparisonProduct['category']) ?></td></tr>
                                                <tr><th scope="row">Precio</th><td><?= e(formatMoney((float)$selectedProduct['price'], $currency)) ?></td><td><?= e(formatMoney((float)$comparisonProduct['price'], $currency)) ?></td></tr>
                                                <tr><th scope="row">Disponibilidad</th><td><?= (int)$selectedProduct['stock'] > 0 ? (int)$selectedProduct['stock'] . ' en stock' : 'Agotado' ?></td><td><?= (int)$comparisonProduct['stock'] > 0 ? (int)$comparisonProduct['stock'] . ' en stock' : 'Agotado' ?></td></tr>
                                                <tr><th scope="row">Calificación</th><td><?= number_format((float)$selectedProduct['avg_rating'], 1) ?> / 5 (<?= (int)$selectedProduct['review_count'] ?> reseñas)</td><td><?= number_format((float)$comparisonProduct['avg_rating'], 1) ?> / 5 (<?= (int)$comparisonProduct['review_count'] ?> reseñas)</td></tr>
                                                <tr><th scope="row">Descripción</th><td><?= nl2br(e($selectedProduct['description'])) ?></td><td><?= nl2br(e($comparisonProduct['description'])) ?></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </section>
                            <section class="product-inquiry panel-card">
                                <div><span class="eyebrow">¿TIENES DUDAS?</span><h2>Consulta sobre este producto</h2><p>Código único del producto: <b><?= e($selectedProduct['code']) ?></b>. Puedes enviar tu consulta por correo, iniciar un chat con el administrador o abrir su WhatsApp personal.</p></div>
                                <?php if ($user && !isAdmin()): ?>
                                    <form method="post" class="product-inquiry-form" data-chat-product-name="<?= e($selectedProduct['name']) ?>" data-chat-product-code="<?= e($selectedProduct['code']) ?>">
                                        <?= csrfField() ?><input type="hidden" name="action" value="product_inquiry"><input type="hidden" name="product_id" value="<?= (int)$selectedProduct['id'] ?>">
                                        <label for="product-inquiry-message">Tu mensaje</label><textarea id="product-inquiry-message" name="message" rows="3" maxlength="2000" required placeholder="Escribe tu consulta sobre <?= e($selectedProduct['name']) ?>"></textarea>
                                        <div class="product-inquiry-actions">
                                            <button class="button button-primary" type="submit" name="inquiry_channel" value="email"><span class="google-logo-mark" aria-hidden="true">G</span>Enviar por correo</button>
                                            <?php if ($canUseChat): ?><button class="button button-outline" type="button" data-consultation-choice><span class="whatsapp-mark" aria-hidden="true">◉</span>Chat o WhatsApp</button><?php else: ?><button class="button button-outline" type="submit" name="inquiry_channel" value="whatsapp"><span class="whatsapp-mark" aria-hidden="true">◉</span>WhatsApp</button><?php endif; ?>
                                        </div>
                                    </form>
                                <?php else: ?><p class="review-signin"><a href="index.php?page=login">Inicia sesión</a> con el correo registrado para enviar una consulta.</p><?php endif; ?>
                            </section>
                            <section class="reviews-section" id="reviews"><div class="section-heading"><div><span class="eyebrow">OPINIONES REALES</span><h2>Lo que dice la comunidad</h2></div></div>
                        <?php
                            $reviewCount = (int)$selectedProduct['review_count'];
                            $ratingMaxCount = max($ratingDistribution ?: [0]);
                        ?>
                        <div class="review-overview" aria-label="Resumen de calificaciones">
                            <div class="review-average">
                                <strong><?= $reviewCount > 0 ? number_format((float)$selectedProduct['avg_rating'], 1) : '—' ?></strong>
                                <span class="review-average-stars" aria-label="<?= $reviewCount > 0 ? e(number_format((float)$selectedProduct['avg_rating'], 1) . ' de 5 estrellas') : 'Sin calificaciones' ?>"><?= str_repeat('★', (int)round((float)$selectedProduct['avg_rating'])) ?><?= str_repeat('☆', 5 - (int)round((float)$selectedProduct['avg_rating'])) ?></span>
                                <small><?= $reviewCount ?> <?= $reviewCount === 1 ? 'reseña' : 'reseñas' ?></small>
                            </div>
                            <div class="review-distribution" aria-label="Distribución de reseñas">
                                <?php for ($star = 5; $star >= 1; $star--): ?>
                                    <?php $starCount = (int)($ratingDistribution[$star] ?? 0); $barWidth = $ratingMaxCount > 0 ? ($starCount / $ratingMaxCount) * 100 : 0; ?>
                                    <div class="review-distribution-row" aria-label="<?= $star ?> <?= $star === 1 ? 'estrella' : 'estrellas' ?>: <?= $starCount ?> <?= $starCount === 1 ? 'reseña' : 'reseñas' ?>">
                                        <span><?= $star ?> <span aria-hidden="true">★</span></span>
                                        <div class="review-distribution-track" aria-hidden="true"><i style="width: <?= e(number_format($barWidth, 2, '.', '')) ?>%"></i></div>
                                        <small><?= $starCount ?></small>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if ($user): ?><form method="post" class="panel-card review-form"><?= csrfField() ?><input type="hidden" name="action" value="review"><input type="hidden" name="product_id" value="<?= (int)$selectedProduct['id'] ?>"><label>Tu calificación<select required name="rating"><option value="">Selecciona estrellas</option><?php for ($star = 5; $star >= 1; $star--): ?><option value="<?= $star ?>"><?= str_repeat('★', $star) ?> (<?= $star ?>)</option><?php endfor; ?></select></label><label>Cuéntanos tu experiencia<textarea required name="comment" rows="3" placeholder="¿Qué te pareció este producto?"></textarea></label><button class="button button-primary">Publicar reseña</button></form><?php else: ?><p class="review-signin"><a href="index.php?page=login">Inicia sesión</a> para dejar tu reseña y calificar comentarios.</p><?php endif; ?>
                        <?php if (!$reviews): ?><p class="muted">Aún no hay reseñas. ¡Sé la primera persona en opinar!</p><?php endif; ?>
                        <div class="review-list"><?php foreach ($reviews as $review): ?><article class="review-card"><div class="review-avatar"><?= e(mb_strtoupper(mb_substr($review['username'], 0, 1))) ?></div><div class="review-body"><div class="review-head"><b><?= e($review['username']) ?></b><span><?= e(date('d/m/Y', strtotime($review['created_at']))) ?></span></div><div class="review-stars"><?= str_repeat('★', (int)$review['rating']) ?><?= str_repeat('☆', 5 - (int)$review['rating']) ?></div><p><?= nl2br(e($review['comment'])) ?></p><div class="vote-actions"><?php foreach ([1 => '👍', -1 => '👎'] as $vote => $emoji): ?><form method="post"><?= csrfField() ?><input type="hidden" name="action" value="vote"><input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>"><input type="hidden" name="product_id" value="<?= (int)$selectedProduct['id'] ?>"><input type="hidden" name="vote" value="<?= $vote ?>"><button class="vote-button" title="<?= $vote === 1 ? 'Me gusta' : 'No me gusta' ?>"><?= $emoji ?> <?= (int)($vote === 1 ? $review['likes'] : $review['dislikes']) ?></button></form><?php endforeach; ?></div></div></article><?php endforeach; ?></div>
                            </section>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="product-grid" id="product-grid"><?php foreach ($products as $product): ?><?php require __DIR__ . '/partials/product-card.php'; ?><?php endforeach; ?></div><div class="panel-card empty-state catalog-empty" id="catalog-empty" <?= $matchingProducts > 0 ? 'hidden' : '' ?>><span><svg class="ico" aria-hidden="true"><use href="#i-search"/></svg></span><b>No encontramos productos con esos filtros</b><p>Prueba con otra búsqueda o vuelve a ver todo el catálogo.</p><a class="button button-quiet" href="index.php">Ver todos</a></div>
            </section>
            <section class="benefits-strip" data-catalog-promo <?= $search !== '' || $categoryFilter !== '' ? 'hidden' : '' ?>><div><span><svg class="ico" aria-hidden="true"><use href="#i-heart"/></svg></span><b>Atención cercana</b><small>Estamos para ayudarte</small></div><div><span><svg class="ico" aria-hidden="true"><use href="#i-shield"/></svg></span><b>Compra con confianza</b><small>Pedidos con seguimiento</small></div><div><span><svg class="ico" aria-hidden="true"><use href="#i-trend"/></svg></span><b>Pago a tu manera</b><small>Opciones para ti</small></div></section>
        <?php endif; ?>
        <footer class="site-footer" aria-label="Pie de página">
            <div class="footer-content">
                <div class="footer-brand">
                    <a class="footer-brand-link" href="index.php" aria-label="GoStore">
                        <span class="footer-brand-mark">G</span>
                        <span class="footer-brand-name">Go<span>Store</span><small>TODO, EN UN SOLO LUGAR</small></span>
                    </a>
                    <p>Compra local, compra mejor.</p>
                </div>
                <nav class="footer-nav" aria-label="Enlaces rápidos">
                    <h2>Enlaces rápidos</h2>
                    <a href="index.php">Inicio</a>
                    <a href="index.php?page=store#productos">Catálogo</a>
                    <?php if (isAdmin()): ?>
                        <a href="index.php?page=admin">Panel de administración</a>
                    <?php elseif ($user): ?>
                        <a href="index.php?page=orders">Mis pedidos</a>
                        <a href="index.php?page=profile">Mi perfil</a>
                    <?php else: ?>
                        <a href="index.php?page=login">Iniciar sesión</a>
                        <a href="index.php?page=register">Crear cuenta</a>
                    <?php endif; ?>
                </nav>
                <section class="footer-contact" aria-labelledby="footer-contact-title">
                    <h2 id="footer-contact-title">Contacto</h2>
                    <p>Atención personalizada por WhatsApp y correo.</p>
                    <a class="footer-contact-link" href="tel:+<?= e($adminPhone) ?>">+<?= e($adminPhone) ?></a>
                    <a class="footer-contact-link" href="mailto:<?= e($adminEmail) ?>"><?= e($adminEmail) ?></a>
                    <a class="footer-whatsapp" href="https://wa.me/<?= e($adminPhone) ?>?text=<?= rawurlencode($whatsappMessage) ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp" title="Escríbenos por WhatsApp">Escríbenos por WhatsApp</a>
                </section>
            </div>
            <div class="footer-bottom">
                <span>© <?= date('Y') ?> GoStore</span>
                <span>Compra local, compra mejor.</span>
            </div>
        </footer>
    </main>
</div>
    <?php if ($user && $canUseChat): ?>
        <section class="chat-widget" data-chat-widget data-role="<?= isSuperAdmin() ? 'superadmin' : (isAdmin() ? 'admin' : 'user') ?>" data-user-id="<?= (int)$user['id'] ?>" data-user-name="<?= e($user['full_name']) ?>" data-user-email="<?= e($user['email']) ?>" data-admin-phone="<?= e($adminPhone) ?>" data-endpoint="index.php?chat=1" aria-label="Chat de GoStore">
        <button class="chat-launcher" type="button" data-chat-toggle aria-expanded="false">
            <span class="chat-launcher-icon" aria-hidden="true">✉</span>
            <span><?= isAdmin() ? 'Mensajes' : 'Chat con admin' ?></span>
            <i class="chat-presence-dot" data-chat-launcher-status aria-label="Estado de conexión"></i>
            <span class="chat-unread-badge" data-chat-unread hidden></span>
        </button>
        <section class="chat-window" data-chat-window hidden aria-label="Conversaciones">
            <header class="chat-header">
                <div><b data-chat-title><?= isAdmin() ? 'Atención al cliente' : 'Chat con el administrador' ?></b><span data-chat-status>Conectando…</span></div>
                <button class="chat-close" type="button" data-chat-close aria-label="Cerrar chat">×</button>
            </header>
            <div class="chat-body <?= isAdmin() ? 'is-admin' : '' ?>">
                <?php if (isAdmin()): ?><aside class="chat-user-list"><b>Usuarios</b><div data-chat-users><p class="chat-empty-users">Buscando usuarios conectados…</p></div></aside><?php endif; ?>
                <section class="chat-conversation">
                    <div class="chat-peer-heading"><b data-chat-peer-title><?= isAdmin() ? 'Selecciona un usuario' : 'Administrador' ?></b><span data-chat-peer-status></span></div>
                    <p class="chat-notice" data-chat-notice hidden role="status"></p>
                    <div class="chat-messages" data-chat-messages aria-live="polite" aria-relevant="additions"></div>
                    <p class="chat-typing" data-chat-typing <?= isSuperAdmin() ? 'hidden' : '' ?>>Escribiendo…</p>
                    <?php if (!isSuperAdmin()): ?><form class="chat-compose" data-chat-form enctype="multipart/form-data">
                        <div class="chat-attachment-preview" data-chat-attachment-preview hidden></div>
                        <div class="chat-compose-tools">
                            <?php if (isAdmin()): ?><button class="chat-template-button" type="button" data-chat-order-template><svg class="ico" aria-hidden="true"><use href="#i-receipt"/></svg><span>Plantilla de pedido</span></button><?php endif; ?>
                            <label class="chat-attach-button" title="Adjuntar imagen o video">＋<input type="file" name="attachment" data-chat-attachment accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"></label>
                            <button class="chat-emoji-toggle" type="button" data-chat-emoji-toggle aria-label="Elegir emoji">😊</button>
                            <div class="chat-emoji-picker" data-chat-emoji-picker hidden><?php foreach (['😀','😂','😍','🥰','😊','😉','😎','😭','😡','👍','👎','👏','🙏','❤️','🎉','🔥','✨','👋','✅','💯'] as $emoji): ?><button type="button" data-chat-emoji="<?= e($emoji) ?>"><?= e($emoji) ?></button><?php endforeach; ?></div>
                        </div>
                        <textarea name="message_text" data-chat-composer rows="2" maxlength="3000" placeholder="Escribe un mensaje…" aria-label="Escribe un mensaje"></textarea>
                        <button class="chat-send-button" type="submit" aria-label="Enviar mensaje">➤</button>
                    </form>
                    <small class="chat-upload-help">Imágenes o videos · máximo 20 MB</small><?php endif; ?>
                    <?php if (!isSuperAdmin()): ?><div class="chat-message-menu" data-chat-message-menu hidden role="menu" aria-label="Opciones del mensaje">
                        <button type="button" data-chat-message-edit-action role="menuitem">Editar mensaje</button>
                        <button type="button" class="danger" data-chat-message-delete-action role="menuitem">Eliminar mensaje</button>
                    </div><?php endif; ?>
                </section>
            </div>
            <div class="chat-delete-confirm" data-chat-delete-confirm hidden role="alertdialog" aria-modal="true" aria-labelledby="chat-delete-title" aria-describedby="chat-delete-message">
                <section class="chat-delete-confirm-card">
                    <span class="chat-delete-confirm-icon" aria-hidden="true">!</span>
                    <h2 id="chat-delete-title">Eliminar mensaje</h2>
                    <p id="chat-delete-message">Este mensaje se eliminará para ti y para la otra persona. Esta acción no se puede deshacer.</p>
                    <div class="chat-delete-confirm-actions">
                        <button class="button button-quiet" type="button" data-chat-delete-cancel>Cancelar</button>
                        <button class="button button-danger" type="button" data-chat-delete-accept>Eliminar</button>
                    </div>
                </section>
            </div>
            <div class="chat-delete-confirm chat-message-edit" data-chat-message-edit hidden role="dialog" aria-modal="true" aria-labelledby="chat-message-edit-title">
                <section class="chat-delete-confirm-card chat-message-edit-card">
                    <span class="chat-message-edit-icon" aria-hidden="true">✎</span>
                    <h2 id="chat-message-edit-title">Editar mensaje</h2>
                    <label class="chat-message-edit-label" for="chat-message-edit-text">Modifica el texto de tu mensaje</label>
                    <textarea class="chat-message-edit-textarea" id="chat-message-edit-text" data-chat-message-edit-text maxlength="3000" rows="5"></textarea>
                    <p class="chat-message-edit-error" data-chat-message-edit-error hidden role="alert"></p>
                    <div class="chat-delete-confirm-actions">
                        <button class="button button-quiet" type="button" data-chat-message-edit-cancel>Cancelar</button>
                        <button class="button button-primary" type="button" data-chat-message-edit-save>Guardar cambios</button>
                    </div>
                </section>
            </div>
        </section>
        <form hidden data-chat-csrf><?= csrfField() ?></form>
    </section>
<?php endif; ?>
<?php if ($user && !isAdmin() && $canUseChat): ?>
    <div class="chat-choice-overlay" data-chat-choice data-admin-phone="<?= e($adminPhone) ?>" hidden role="dialog" aria-modal="true" aria-labelledby="chat-choice-title">
        <section class="chat-choice-card">
            <button type="button" class="chat-close" data-chat-choice-close aria-label="Cerrar">×</button>
            <span class="eyebrow">ATENCIÓN PERSONALIZADA</span>
            <h2 id="chat-choice-title">¿Cómo deseas enviar tu consulta?</h2>
            <p>Elige una opción para continuar con el administrador.</p>
            <div class="chat-choice-actions">
                <button type="button" class="button button-primary" data-chat-choice-send>Enviar por chat</button>
                <button type="button" class="button button-outline" data-chat-choice-whatsapp>WhatsApp personal</button>
            </div>
        </section>
    </div>
<?php endif; ?>
<div class="system-loader" id="system-loader" role="status" aria-live="polite" aria-hidden="true" hidden>
    <div class="loader-card"><span class="loader-spinner" aria-hidden="true"></span><b id="loader-message">Cargando GoStore</b><small>Un momento, por favor</small></div>
</div>
<div class="confirm-overlay" id="confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message" hidden>
    <div class="confirm-card"><span class="confirm-icon" aria-hidden="true">!</span><h2 id="confirm-title">Confirmar acción</h2><p id="confirm-message"></p><div class="confirm-actions"><button class="button button-quiet" type="button" data-confirm-cancel>Cancelar</button><button class="button button-danger" type="button" data-confirm-accept>Confirmar</button></div></div>
</div>
<?php if ($showUserGuide): ?>
    <div class="modal-overlay guide-overlay" role="dialog" aria-modal="true" aria-labelledby="guide-title">
        <form method="post" class="modal-card guide-card">
            <input type="hidden" name="action" value="guide_dismiss"><?= csrfField() ?>
            <button class="guide-close" type="submit" aria-label="Cerrar guía">×</button>
            <span class="modal-icon">✦</span>
            <span class="eyebrow">GUÍA DE INICIO</span>
            <h2 id="guide-title">¡Bienvenido a GoStore!</h2>
            <p>Esta guía te explica las partes principales de la tienda. Cuando termines, podrás volver a explorar los productos.</p>
            <ol class="guide-steps">
                <li><b>Busca y descubre</b><span>Usa la barra de búsqueda o las categorías del menú. Selecciona un producto para ver sus detalles, reseñas y disponibilidad.</span></li>
                <li><b>Prepara tu carrito</b><span>En la ficha del producto, elige la cantidad y agrégalo al carrito. Puedes cambiar cantidades o quitar productos antes de pagar.</span></li>
                <li><b>Completa la compra</b><span>En el carrito, indica la dirección de entrega, elige un medio de pago y confirma el pedido.</span></li>
                <li><b>Revisa el seguimiento</b><span>Abre “Mis pedidos” para consultar el estado. Recibirás notificaciones sobre novedades de productos y pedidos.</span></li>
                <li><b>Personaliza tu cuenta</b><span>En “Mi perfil” guarda tus datos de contacto. En “Ajustes” elige el idioma y uno de los cuatro estilos de color.</span></li>
            </ol>
            <button class="button button-primary button-wide" type="submit">¡Entendido, empezar a comprar!</button>
        </form>
    </div>
<?php endif; ?>
<?php if (!empty($_SESSION['order_whatsapp'])): ?><div class="modal-overlay" id="whatsapp-modal"><div class="modal-card"><span class="modal-icon">✓</span><h2>¡Pedido confirmado!</h2><p>Tu compra contra entrega está registrada. Ahora puedes avisarnos por WhatsApp para coordinar la entrega.</p><a class="button button-primary button-wide whatsapp-confirm-button" href="<?= e($_SESSION['order_whatsapp']) ?>" target="_blank" rel="noopener" aria-label="Continuar a WhatsApp" title="Continuar a WhatsApp"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20.52 3.48A11.82 11.82 0 0 0 12.08 0C5.48 0 .1 5.38.1 12c0 2.12.55 4.18 1.6 6L.04 24l6.14-1.61A11.9 11.9 0 0 0 12.07 24C18.67 24 24 18.62 24 12c0-3.2-1.24-6.2-3.48-8.52Zm-8.44 18.34a9.8 9.8 0 0 1-5-1.37l-.36-.21-3.64.95.97-3.55-.23-.37A9.78 9.78 0 1 1 12.08 21.82Zm5.37-7.33c-.29-.15-1.74-.86-2.01-.96-.27-.1-.47-.15-.67.15-.2.29-.77.96-.94 1.16-.17.2-.35.22-.64.07-.29-.15-1.23-.45-2.35-1.45-.87-.77-1.45-1.73-1.62-2.02-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.2-.29.29-.49.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.91-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.29-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.74-.71 1.99-1.4.25-.68.25-1.27.17-1.39-.07-.12-.27-.2-.56-.34Z"/></svg></a><button class="text-button modal-close" data-dismiss-modal>Cerrar</button></div></div><?php unset($_SESSION['order_whatsapp']); endif; ?>
<?php if (!empty($_SESSION['order_receipt'])): ?>
    <div class="modal-overlay" id="order-receipt-modal" role="dialog" aria-modal="true" aria-labelledby="order-receipt-title">
        <div class="modal-card receipt-modal-card">
            <span class="modal-icon">✓</span>
            <h2 id="order-receipt-title">¡Pedido confirmado!</h2>
            <p>Boleta de compra a nombre de <?= e($user['full_name']) ?>:</p>
            <strong class="order-code"><?= e($_SESSION['order_receipt']['code']) ?></strong>
            <pre class="receipt-preview"><?= e($_SESSION['order_receipt']['text']) ?></pre>
            <div class="receipt-share-actions receipt-modal-actions">
                <a class="button button-outline receipt-share-button" href="<?= e($_SESSION['order_receipt']['whatsapp_url']) ?>" target="_blank" rel="noopener"><svg class="whatsapp-icon" aria-hidden="true" viewBox="0 0 24 24"><path d="M20.52 3.48A11.82 11.82 0 0 0 12.08 0C5.48 0 .1 5.38.1 12c0 2.12.55 4.18 1.6 6L.04 24l6.14-1.61A11.9 11.9 0 0 0 12.07 24C18.67 24 24 18.62 24 12c0-3.2-1.24-6.2-3.48-8.52ZM12.08 21.82a9.8 9.8 0 0 1-5-1.37l-.36-.21-3.64.95.97-3.55-.23-.37A9.78 9.78 0 1 1 12.08 21.82Zm5.37-7.33c-.29-.15-1.74-.86-2.01-.96-.27-.1-.47-.15-.67.15-.2.29-.77.96-.94 1.16-.17.2-.35.22-.64.07-.29-.15-1.23-.45-2.35-1.45-.87-.77-1.45-1.73-1.62-2.02-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.2-.29.29-.49.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.91-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.29-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.17.29 2.1 3.21 5.09 4.5.71.31 1.36.2 1.87.12.57-.08 1.74-.71 1.99-1.4.25-.68.17-1.27.17-1.39-.07-.12-.27-.2-.56-.34Z"/></svg>WhatsApp</a>
                <a class="button button-outline receipt-share-button" href="<?= e($_SESSION['order_receipt']['gmail_url']) ?>" target="_blank" rel="noopener"><span class="google-logo-mark" aria-hidden="true">G</span>Enviar por Gmail</a>
            </div>
            <p class="receipt-help">La boleta completa con el código del pedido y cada producto también está en “Mis pedidos”.</p>
            <button class="text-button modal-close" data-dismiss-modal>Cerrar</button>
        </div>
    </div>
    <?php unset($_SESSION['order_receipt']); ?>
<?php endif; ?>
<script src="translations.js?v=5"></script>
<script src="app.js?v=27"></script>
<script>
(() => {
    const allowedThemes = ['violeta', 'naranja', 'verde', 'azul', 'oscuro'];
    const allowedLanguages = ['es', 'en', 'fr', 'pt', 'it'];
    const storedTheme = localStorage.getItem('gostore-theme');
    const storedLanguage = localStorage.getItem('gostore-language');
    const hasAccountPreferences = document.body.dataset.userAuthenticated === 'true';
    const serverTheme = document.body.dataset.theme;
    const serverLanguage = document.body.dataset.lang;
    const theme = hasAccountPreferences && allowedThemes.includes(serverTheme)
        ? serverTheme
        : (allowedThemes.includes(storedTheme || '') ? storedTheme : 'violeta');
    const language = hasAccountPreferences && allowedLanguages.includes(serverLanguage)
        ? serverLanguage
        : (allowedLanguages.includes(storedLanguage || '') ? storedLanguage : 'es');
    document.body.dataset.theme = theme;
    document.body.dataset.lang = language;
    const themeColors = { violeta: '#7657f6', naranja: '#ff8b2c', verde: '#1bc98b', azul: '#2b7cff', oscuro: '#111827' };
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', themeColors[theme] || themeColors.violeta);
    document.documentElement.lang = language;
    localStorage.setItem('gostore-theme', theme);
    localStorage.setItem('gostore-language', language);
})();
</script>
</body>
</html>
