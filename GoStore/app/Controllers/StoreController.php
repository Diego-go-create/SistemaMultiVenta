<?php
declare(strict_types=1);

final class StoreController
{
    public function __construct(private StoreModel $store)
    {
    }

    public function render(): void
    {
        $requestedPage = (string)($_GET['page'] ?? (isSuperAdmin() ? 'superadmin' : (isAdmin() ? 'admin' : 'store')));
        $page = in_array($requestedPage, ['store', 'login', 'register', 'admin', 'admin-products', 'admin-carousel', 'admin-orders', 'admin-users', 'superadmin', 'profile', 'settings', 'orders', 'favorites', 'cart'], true)
            ? $requestedPage
            : 'store';
        if ($page === 'superadmin') {
            requireSuperAdmin();
        }
        if (isAdmin() && in_array($page, ['cart', 'favorites'], true)) {
            go('index.php?page=' . (isSuperAdmin() ? 'superadmin' : 'admin'));
        }
        if (in_array($page, ['admin', 'admin-products', 'admin-carousel', 'admin-orders', 'admin-users'], true)) {
            requireAdmin();
            $pagePermissions = [
                'admin-products' => 'admin_products',
                'admin-carousel' => 'admin_carousel',
                'admin-orders' => 'admin_orders',
                'admin-users' => 'admin_users',
            ];
            if (isset($pagePermissions[$page]) && !isSuperAdmin() && !isOperationEnabled(db(), $pagePermissions[$page])) {
                http_response_code(403);
                exit('Esta sección está temporalmente restringida por el SuperAdmin.');
            }
        } elseif ($page === 'profile' || $page === 'settings' || $page === 'orders' || $page === 'favorites') {
            requireLogin();
            if ($page === 'favorites') {
                requireCustomer();
            }
        }

        $user = currentUser();
        if ($user && !array_key_exists('presentation_shown', $_SESSION)) {
            $_SESSION['presentation_shown'] = false;
        }
        $appearance = resolveUserAppearance($user);
        $language = $appearance['language'];
        $theme = $appearance['theme'];
        $currency = $appearance['currency'];
        $canUseChat = $user !== null && (isSuperAdmin() || isOperationEnabled(db(), isAdmin() ? 'admin_chat' : 'user_chat'));
        $adminPageAccess = [
            'admin_products' => true,
            'admin_carousel' => true,
            'admin_orders' => true,
            'admin_users' => true,
        ];
        if (isAdmin() && !isSuperAdmin()) {
            foreach (array_keys($adminPageAccess) as $permissionKey) {
                $adminPageAccess[$permissionKey] = isOperationEnabled(db(), $permissionKey);
            }
        }
        $whatsappMessage = 'Hola, quisiera consultar sus productos.';
        if ($user) {
            $customerName = trim((string)($user['full_name'] ?? $user['username'] ?? ''));
            if ($customerName !== '') {
                $whatsappMessage .= ' Mi nombre es ' . $customerName . '.';
            }
        }

        $cart = isAdmin() ? [] : ($_SESSION['cart'] ?? []);
        $cartCount = isAdmin() ? 0 : array_sum($cart);
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $categories = $this->store->getCategories();
        $search = trim((string)($_GET['q'] ?? ''));
        $categoryFilter = trim((string)($_GET['category'] ?? ''));
        $selectedProductId = filter_input(INPUT_GET, 'product', FILTER_VALIDATE_INT);
        $compareProductId = filter_var($_GET['compare'] ?? null, FILTER_VALIDATE_INT);
        $data = [
            'user' => $user,
            'page' => $page,
            'adminEmail' => getAdminEmail(db()),
            'adminPhone' => getAdminPhone(db()),
            'language' => $language,
            'theme' => $theme,
            'currency' => $currency,
            'canUseChat' => $canUseChat,
            'adminPageAccess' => $adminPageAccess,
            'whatsappMessage' => $whatsappMessage,
            'cartCount' => $cartCount,
            'flash' => $flash,
            'categories' => $categories,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
            'showUserGuide' => !isAdmin() && !empty($_SESSION['show_user_guide']),
        ];
        if ($user) {
            $data += $this->store->getNotifications((int)$user['id']);
        } else {
            $data += ['notifications' => [], 'unreadCount' => 0];
        }
        if (!isAdmin()) {
            $data += $this->store->getCartData($cart);
        } else {
            $data += ['cartItems' => [], 'cartTotal' => 0.0];
        }

        if ($page === 'store') {
            $data += $this->store->getStorefrontData(
                $search,
                $categoryFilter,
                isAdmin() ? null : ($selectedProductId ?: null),
                $user && !isAdmin() ? (int)$user['id'] : null,
                $compareProductId ?: null
            );
        } elseif ($page === 'admin') {
            $data += $this->store->getAdminSummary();
        } elseif ($page === 'admin-products') {
            $editingProductId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
            $data += $this->store->getAdminProducts($editingProductId ?: null);
        } elseif ($page === 'admin-carousel') {
            $data['carouselImages'] = $this->store->getCarouselImages();
        } elseif ($page === 'admin-orders') {
            $data['orders'] = $this->store->getAdminOrders();
        } elseif ($page === 'admin-users') {
            $data['users'] = $this->store->getAdminUsers();
        } elseif ($page === 'superadmin') {
            $auditPage = filter_var($_GET['audit_page'] ?? 1, FILTER_VALIDATE_INT);
            $data += $this->store->getSuperAdminData($auditPage && $auditPage > 0 ? $auditPage : 1);
        } elseif ($page === 'orders') {
            $data['myOrders'] = $this->store->getUserOrders((int)$user['id']);
        } elseif ($page === 'favorites') {
            $data['products'] = $this->store->getFavoriteProducts((int)$user['id']);
            $data['matchingProducts'] = count($data['products']);
        }

        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/store.php';
    }
}
