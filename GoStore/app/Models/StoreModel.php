<?php
declare(strict_types=1);

final class StoreModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getCategories(): array
    {
        return $this->pdo->query('SELECT DISTINCT category FROM products ORDER BY category')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getNotifications(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, type, message, link, created_at, read_at FROM notifications WHERE recipient_user_id = ? ORDER BY created_at DESC, id DESC LIMIT 10');
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll();
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM notifications WHERE recipient_user_id = ? AND read_at IS NULL');
        $stmt->execute([$userId]);
        $unreadCount = (int)$stmt->fetchColumn();

        return compact('notifications', 'unreadCount');
    }

    public function getStorefrontData(string $search, string $categoryFilter, ?int $selectedProductId, ?int $userId = null, ?int $compareProductId = null): array
    {
        $selectedProduct = null;
        $comparisonProduct = null;
        $reviews = [];
        $ratingDistribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        if ($selectedProductId) {
            $stmt = $this->pdo->prepare('SELECT p.*, COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS review_count FROM products p LEFT JOIN reviews r ON r.product_id = p.id WHERE p.id = ? GROUP BY p.id');
            $stmt->execute([$selectedProductId]);
            $selectedProduct = $stmt->fetch() ?: null;
            if ($selectedProduct) {
                $stmt = $this->pdo->prepare('SELECT rating, COUNT(*) AS rating_count FROM reviews WHERE product_id = ? GROUP BY rating');
                $stmt->execute([$selectedProductId]);
                foreach ($stmt->fetchAll() as $distributionRow) {
                    $ratingDistribution[(int)$distributionRow['rating']] = (int)$distributionRow['rating_count'];
                }
                $stmt = $this->pdo->prepare("SELECT r.*, u.username, COALESCE(SUM(CASE WHEN v.vote = 1 THEN 1 ELSE 0 END), 0) AS likes, COALESCE(SUM(CASE WHEN v.vote = -1 THEN 1 ELSE 0 END), 0) AS dislikes FROM reviews r JOIN users u ON u.id = r.user_id LEFT JOIN review_votes v ON v.review_id = r.id WHERE r.product_id = ? GROUP BY r.id ORDER BY r.created_at DESC");
                $stmt->execute([$selectedProductId]);
                $reviews = $stmt->fetchAll();
                if ($compareProductId && $compareProductId !== $selectedProductId) {
                    $stmt = $this->pdo->prepare('SELECT p.*, COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS review_count FROM products p LEFT JOIN reviews r ON r.product_id = p.id WHERE p.id = ? AND p.category = ? GROUP BY p.id');
                    $stmt->execute([$compareProductId, $selectedProduct['category']]);
                    $comparisonProduct = $stmt->fetch() ?: null;
                }
            }
        }

        $params = [];
        $searchMatch = '1=1';
        if ($search !== '') {
            $term = '%' . $search . '%';
            $searchMatch = '(p.name LIKE ? OR p.description LIKE ? OR p.category LIKE ?)';
            array_push($params, $term, $term, $term);
        }
        $productQuery = 'SELECT p.*, COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS review_count, CASE WHEN ' . $searchMatch . ' THEN 1 ELSE 0 END AS search_match FROM products p LEFT JOIN reviews r ON r.product_id = p.id GROUP BY p.id ORDER BY p.created_at DESC';
        $stmt = $this->pdo->prepare($productQuery);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        $matchingProducts = count(array_filter(
            $products,
            static fn(array $product): bool => (int)$product['search_match'] === 1
                && ($categoryFilter === '' || $product['category'] === $categoryFilter)
        ));

        $favoriteIds = [];
        if ($userId !== null) {
            $stmt = $this->pdo->prepare('SELECT product_id FROM user_favorites WHERE user_id = ?');
            $stmt->execute([$userId]);
            $favoriteIds = array_fill_keys(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)), true);
        }
        foreach ($products as &$product) {
            $product['is_favorite'] = isset($favoriteIds[(int)$product['id']]);
        }
        unset($product);
        if ($selectedProduct) {
            $selectedProduct['is_favorite'] = isset($favoriteIds[(int)$selectedProduct['id']]);
        }

        $featured = $this->pdo->query('SELECT p.*, AVG(r.rating) AS avg_rating, COUNT(r.id) AS review_count FROM products p JOIN reviews r ON r.product_id = p.id GROUP BY p.id HAVING AVG(r.rating) >= 4 ORDER BY avg_rating DESC, review_count DESC LIMIT 8')->fetchAll();
        if (!$featured) {
            $featured = $this->pdo->query('SELECT p.*, COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS review_count FROM products p LEFT JOIN reviews r ON r.product_id = p.id GROUP BY p.id ORDER BY p.created_at DESC LIMIT 8')->fetchAll();
        }
        $featuredReviews = $this->pdo->query('SELECT r.id, r.product_id, r.rating, r.comment, r.created_at, u.username, p.name AS product_name, p.image AS product_image, v.likes FROM reviews r JOIN users u ON u.id = r.user_id JOIN products p ON p.id = r.product_id JOIN (SELECT review_id, COUNT(*) AS likes FROM review_votes WHERE vote = 1 GROUP BY review_id) v ON v.review_id = r.id ORDER BY v.likes DESC, r.created_at DESC LIMIT 5')->fetchAll();
        $carouselImages = $this->getCarouselImages();

        $imageMap = $this->getProductImageMap(array_merge(
            $products,
            $featured,
            $selectedProduct ? [$selectedProduct] : [],
            $comparisonProduct ? [$comparisonProduct] : []
        ));
        foreach ($products as &$product) {
            $product['images'] = $imageMap[(int)$product['id']] ?? [];
        }
        unset($product);
        foreach ($featured as &$product) {
            $product['images'] = $imageMap[(int)$product['id']] ?? [];
        }
        unset($product);
        if ($selectedProduct) {
            $selectedProduct['images'] = $imageMap[(int)$selectedProduct['id']] ?? [];
        }
        if ($comparisonProduct) {
            $comparisonProduct['images'] = $imageMap[(int)$comparisonProduct['id']] ?? [];
        }

        return compact('selectedProduct', 'comparisonProduct', 'reviews', 'ratingDistribution', 'products', 'matchingProducts', 'featured', 'featuredReviews', 'carouselImages')
            + ['carouselSlideCount' => count($carouselImages) + count($featured) + count($featuredReviews)];
    }

    public function getFavoriteProducts(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT p.*, COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS review_count, f.created_at AS favorite_created_at FROM user_favorites f JOIN products p ON p.id = f.product_id LEFT JOIN reviews r ON r.product_id = p.id WHERE f.user_id = ? GROUP BY p.id, f.created_at ORDER BY f.created_at DESC');
        $stmt->execute([$userId]);
        $products = $stmt->fetchAll();
        foreach ($products as &$product) {
            $product['is_favorite'] = true;
        }
        unset($product);
        $imageMap = $this->getProductImageMap($products);
        foreach ($products as &$product) {
            $product['images'] = $imageMap[(int)$product['id']] ?? [];
        }
        unset($product);

        return $products;
    }

    private function getProductImageMap(array $products): array
    {
        $imagesByProduct = [];
        $fallbackImages = [];
        foreach ($products as $product) {
            $productId = (int)$product['id'];
            $fallbackImages[$productId] = (string)($product['image'] ?? '');
        }
        if (!$fallbackImages) {
            return [];
        }

        $productIds = array_keys($fallbackImages);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $this->pdo->prepare("SELECT product_id, image FROM product_images WHERE product_id IN ({$placeholders}) ORDER BY sort_order, id");
        $stmt->execute($productIds);
        foreach ($stmt->fetchAll() as $row) {
            $imagesByProduct[(int)$row['product_id']][] = (string)$row['image'];
        }
        foreach ($fallbackImages as $productId => $image) {
            if (empty($imagesByProduct[$productId]) && $image !== '') {
                $imagesByProduct[$productId] = [$image];
            }
        }

        return $imagesByProduct;
    }

    public function getCartData(array $cart): array
    {
        $cartItems = [];
        $cartTotal = 0.0;
        if ($cart) {
            $ids = array_map('intval', array_keys($cart));
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id IN ({$placeholders})");
            $stmt->execute($ids);
            foreach ($stmt->fetchAll() as $product) {
                $quantity = (int)$cart[$product['id']];
                $product['quantity'] = $quantity;
                $product['line_total'] = (float)$product['price'] * $quantity;
                $cartTotal += $product['line_total'];
                $cartItems[] = $product;
            }
        }

        return compact('cartItems', 'cartTotal');
    }

    public function getCarouselImages(): array
    {
        return $this->pdo->query('SELECT id, image, caption FROM carousel_images ORDER BY created_at DESC, id DESC')
            ->fetchAll();
    }

    public function getAdminSummary(): array
    {
        $stats = [
            'users' => (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
            'sales' => (float)$this->pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'Cancelado'")->fetchColumn(),
            'orders' => (int)$this->pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pendiente' AND deleted_at IS NULL")->fetchColumn(),
            'products' => (int)$this->pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        ];

        return compact('stats');
    }

    public function getAdminProducts(?int $editingProductId): array
    {
        $allProducts = $this->pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
        $editing = null;
        if ($editingProductId) {
            $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$editingProductId]);
            $editing = $stmt->fetch() ?: null;
        }

        $imageMap = $this->getProductImageMap(array_merge($allProducts, $editing ? [$editing] : []));
        foreach ($allProducts as &$product) {
            $product['images'] = $imageMap[(int)$product['id']] ?? [];
        }
        unset($product);
        if ($editing) {
            $editing['images'] = $imageMap[(int)$editing['id']] ?? [];
        }

        return compact('allProducts', 'editing');
    }

    public function getAdminOrders(): array
    {
        $orders = $this->pdo->query('SELECT o.*, u.full_name, u.email, u.phone, u.address FROM orders o JOIN users u ON u.id = o.user_id WHERE o.deleted_at IS NULL ORDER BY o.created_at DESC LIMIT 40')->fetchAll();

        return $this->attachOrderItems($orders, false);
    }

    public function getAdminUsers(): array
    {
        return $this->pdo->query("SELECT id, username, full_name, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC, id DESC")->fetchAll();
    }

    public function getSuperAdminData(int $auditPage = 1): array
    {
        $stats = [
            'users' => (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
            'deleted_users' => (int)$this->pdo->query("SELECT COUNT(*) FROM audit_log WHERE action = 'user_account_deleted'")->fetchColumn(),
            'orders' => (int)$this->pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'cancelled_orders' => (int)$this->pdo->query("SELECT COUNT(*) FROM audit_log WHERE action = 'order_cancelled' OR (action = 'order_status_changed' AND details LIKE '%-> Cancelado%')")->fetchColumn(),
            'deleted_orders' => (int)$this->pdo->query("SELECT COUNT(*) FROM audit_log WHERE action = 'order_deleted'")->fetchColumn(),
        ];
        $orders = $this->pdo->query('SELECT o.id, o.code, o.total, o.payment_method, o.status, o.created_at, o.deleted_at, u.username, u.full_name FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC')->fetchAll();
        $orders = $this->attachOrderItems($orders, false);
        $auditPageSize = 100;
        $auditCount = (int)$this->pdo->query('SELECT COUNT(*) FROM audit_log')->fetchColumn();
        $auditPageCount = max(1, (int)ceil($auditCount / $auditPageSize));
        $auditPage = min(max(1, $auditPage), $auditPageCount);
        $auditOffset = ($auditPage - 1) * $auditPageSize;
        $auditStatement = $this->pdo->prepare('SELECT actor_username, actor_role, action, details, created_at FROM audit_log ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?');
        $auditStatement->bindValue(1, $auditPageSize, PDO::PARAM_INT);
        $auditStatement->bindValue(2, $auditOffset, PDO::PARAM_INT);
        $auditStatement->execute();
        $auditLog = $auditStatement->fetchAll();
        $permissionRows = $this->pdo->query('SELECT permission_key, enabled, updated_at FROM app_permissions ORDER BY permission_key')->fetchAll();
        $permissions = [];
        foreach ($permissionRows as $permission) {
            if (array_key_exists($permission['permission_key'], APP_PERMISSIONS)) {
                $permission['label'] = APP_PERMISSIONS[$permission['permission_key']];
                $permissions[] = $permission;
            }
        }
        $orphanedFiles = getOrphanedStoredFiles($this->pdo);

        return compact('stats', 'orders', 'auditLog', 'permissions', 'orphanedFiles', 'auditPage', 'auditPageCount');
    }

    public function getAdminExportData(string $page): array
    {
        if ($page === 'admin') {
            $stats = $this->getAdminSummary()['stats'];
            return [
                'title' => 'Resumen del panel',
                'headers' => ['Indicador', 'Valor'],
                'rows' => [
                    ['Usuarios registrados', $stats['users']],
                    ['Ventas registradas (S/)', number_format($stats['sales'], 2, '.', '')],
                    ['Pedidos pendientes', $stats['orders']],
                    ['Productos publicados', $stats['products']],
                ],
            ];
        }

        if ($page === 'admin-products') {
            $rows = $this->pdo->query('SELECT id, code, name, category, description, price, stock, image, created_at FROM products ORDER BY created_at DESC')->fetchAll();
            return [
                'title' => 'Productos',
                'headers' => ['ID', 'Código', 'Nombre', 'Categoría', 'Descripción', 'Precio (S/)', 'Stock', 'Imagen', 'Fecha de publicación'],
                'rows' => array_map(static fn(array $product): array => array_values($product), $rows),
            ];
        }

        if ($page === 'admin-carousel') {
            $rows = [];
            foreach ($this->getCarouselImages() as $image) {
                $rows[] = ['Imagen', $image['id'], $image['caption'], $image['image'], $image['created_at']];
            }
            return [
                'title' => 'Carrusel',
                'headers' => ['Tipo', 'ID', 'Texto / configuración', 'Archivo', 'Fecha'],
                'rows' => $rows,
            ];
        }

        if ($page === 'admin-orders') {
            $rows = [];
            $orders = $this->pdo->query('SELECT o.*, u.full_name, u.email, u.phone, u.address FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC')->fetchAll();
            foreach ($this->attachOrderItems($orders, false) as $order) {
                $items = array_map(
                    static fn(array $item): string => (int)$item['quantity'] . ' x ' . $item['product_name'] . ' [' . $item['product_code'] . ']',
                    $order['items']
                );
                $rows[] = [
                    $order['code'],
                    $order['id'],
                    $order['created_at'],
                    $order['full_name'],
                    $order['email'],
                    $order['phone'],
                    $order['address'],
                    $order['payment_method'],
                    $order['status'],
                    $order['total'],
                    implode('; ', $items),
                    $order['deleted_at'] ?? '',
                ];
            }
            return [
                'title' => 'Pedidos',
                'headers' => ['Código', 'ID', 'Fecha', 'Cliente', 'Correo electrónico', 'Teléfono', 'Dirección', 'Medio de pago', 'Estado', 'Total (S/)', 'Productos', 'Fecha de retiro de la vista'],
                'rows' => $rows,
            ];
        }

        throw new InvalidArgumentException('La pantalla seleccionada no está disponible para exportación.');
    }

    public function getUserOrders(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);

        return $this->attachOrderItems($stmt->fetchAll(), true);
    }

    private function attachOrderItems(array $orders, bool $includeUnitPrice): array
    {
        foreach ($orders as &$order) {
            $order['items'] = [];
        }
        unset($order);
        if (!$orders) {
            return $orders;
        }

        $orderIds = array_map(static fn(array $order): int => (int)$order['id'], $orders);
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $priceColumn = $includeUnitPrice ? ', unit_price' : '';
        $stmt = $this->pdo->prepare("SELECT order_id, product_code, product_name, quantity{$priceColumn} FROM order_items WHERE order_id IN ({$placeholders})");
        $stmt->execute($orderIds);
        $itemsByOrder = [];
        foreach ($stmt->fetchAll() as $item) {
            $itemsByOrder[(int)$item['order_id']][] = $item;
        }

        foreach ($orders as &$order) {
            $order['items'] = $itemsByOrder[(int)$order['id']] ?? [];
        }
        unset($order);

        return $orders;
    }
}
