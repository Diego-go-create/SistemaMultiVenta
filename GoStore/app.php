<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
session_start();

const ADMIN_PHONE = '925869388';
const ADMIN_EMAIL = 'deopamay@gmail.com';
const ADMIN_USERNAME = 'Admin';
const ADMIN_PASSWORD = '63592922Deo';
const SUPERADMIN_USERNAME = 'GoStoreAd';
const SUPERADMIN_PASSWORD = '925869388GoS';
const SUPERADMIN_EMAIL = 'gostoread@local.invalid';

const APP_PERMISSIONS = [
    'user_register' => 'Registro de cuentas',
    'user_shopping' => 'Carrito y favoritos',
    'user_checkout' => 'Crear pedidos',
    'user_order_cancel' => 'Cancelar pedidos propios',
    'user_order_delete' => 'Eliminar pedidos propios',
    'user_account_delete' => 'Eliminar cuenta propia',
    'user_interaction' => 'Consultas, reseñas y votos',
    'user_chat' => 'Chat de clientes',
    'admin_products' => 'Publicar, editar y eliminar productos',
    'admin_carousel' => 'Administrar carrusel',
    'admin_orders' => 'Cambiar estados y retirar pedidos',
    'admin_users' => 'Eliminar cuentas de clientes',
    'admin_chat' => 'Chat de administración',
];

function appSetting(string $name, string $default = ''): string
{
    $environmentValue = getenv($name);
    if ($environmentValue !== false && $environmentValue !== '') {
        return (string)$environmentValue;
    }

    static $localConfig = null;
    if ($localConfig === null) {
        $configPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'gostore-config.php';
        if (is_file($configPath)) {
            $loadedConfig = require $configPath;
            if (!is_array($loadedConfig)) {
                throw new RuntimeException('El archivo C:\\xampp\\gostore-config.php debe devolver un arreglo PHP.');
            }
            $localConfig = $loadedConfig;
        } else {
            $localConfig = [];
        }
    }

    $value = $localConfig[$name] ?? $_SERVER[$name] ?? $_ENV[$name] ?? $default;
    return is_scalar($value) ? (string)$value : $default;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('GOSTORE_DB_HOST') ?: '127.0.0.1';
    $port = getenv('GOSTORE_DB_PORT') ?: '3306';
    $name = getenv('GOSTORE_DB_NAME') ?: 'gostore';
    $user = getenv('GOSTORE_DB_USER') ?: 'root';
    $pass = getenv('GOSTORE_DB_PASS') ?: '';
    if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
        throw new PDOException('Invalid database name.');
    }
    $server = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    initializeDatabase($pdo);
    return $pdo;
}

function initializeDatabase(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(80) NOT NULL UNIQUE,
        full_name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        phone VARCHAR(30) NOT NULL DEFAULT '',
        address VARCHAR(255) NOT NULL DEFAULT '',
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin','superadmin','user') NOT NULL DEFAULT 'user',
        language VARCHAR(10) NOT NULL DEFAULT 'es',
        theme VARCHAR(30) NOT NULL DEFAULT 'violeta',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $userColumns = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('language', $userColumns, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN language VARCHAR(10) NOT NULL DEFAULT 'es'");
    }
    if (!in_array('theme', $userColumns, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN theme VARCHAR(30) NOT NULL DEFAULT 'violeta'");
    }
    if (!in_array('currency', $userColumns, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN currency VARCHAR(3) NOT NULL DEFAULT 'PEN'");
    }
    $pdo->exec("ALTER TABLE users MODIFY role ENUM('admin','superadmin','user') NOT NULL DEFAULT 'user'");
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        actor_user_id INT UNSIGNED NULL,
        actor_username VARCHAR(80) NOT NULL,
        actor_role VARCHAR(20) NOT NULL,
        action VARCHAR(80) NOT NULL,
        details VARCHAR(500) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_audit_log_created (created_at, id),
        INDEX idx_audit_log_action (action, created_at)
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS app_permissions (
        permission_key VARCHAR(60) PRIMARY KEY,
        enabled TINYINT(1) NOT NULL DEFAULT 1,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS superadmin_identity (
        singleton_id TINYINT UNSIGNED PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL UNIQUE,
        CONSTRAINT fk_superadmin_identity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB");
    $permissionInsert = $pdo->prepare('INSERT IGNORE INTO app_permissions (permission_key, enabled) VALUES (?, 1)');
    foreach (array_keys(APP_PERMISSIONS) as $permissionKey) {
        $permissionInsert->execute([$permissionKey]);
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_guide_completions (
        user_id INT UNSIGNED PRIMARY KEY,
        completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_guide_completion_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        recipient_user_id INT UNSIGNED NOT NULL,
        type VARCHAR(30) NOT NULL,
        message VARCHAR(255) NOT NULL,
        link VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        read_at TIMESTAMP NULL DEFAULT NULL,
        INDEX idx_notifications_recipient_read_created (recipient_user_id, read_at, created_at),
        CONSTRAINT fk_notifications_recipient FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_presence (
        user_id INT UNSIGNED PRIMARY KEY,
        last_seen DATETIME NOT NULL,
        typing_to_user_id INT UNSIGNED NULL,
        typing_at DATETIME NULL,
        CONSTRAINT fk_chat_presence_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        sender_user_id INT UNSIGNED NOT NULL,
        recipient_user_id INT UNSIGNED NOT NULL,
        message_type ENUM('text','image','video') NOT NULL DEFAULT 'text',
        message_text TEXT NOT NULL,
        media_path VARCHAR(80) NOT NULL DEFAULT '',
        media_mime VARCHAR(50) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        read_at DATETIME NULL DEFAULT NULL,
        INDEX idx_chat_sender_recipient_id (sender_user_id, recipient_user_id, id),
        INDEX idx_chat_recipient_sender_read (recipient_user_id, sender_user_id, read_at),
        CONSTRAINT fk_chat_message_sender FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_chat_message_recipient FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) NOT NULL DEFAULT '',
        name VARCHAR(150) NOT NULL,
        category VARCHAR(80) NOT NULL,
        description TEXT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        stock INT UNSIGNED NOT NULL DEFAULT 0,
        image VARCHAR(255) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $productColumns = $pdo->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('code', $productColumns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN code VARCHAR(20) NOT NULL DEFAULT '' AFTER id");
    }
    $pdo->exec("UPDATE products SET code = CONCAT('PR-', LPAD(id, 6, '0')) WHERE code = ''");
    $productIndexes = $pdo->query('SHOW INDEX FROM products')->fetchAll(PDO::FETCH_ASSOC);
    $hasUniqueProductCode = count(array_filter(
        $productIndexes,
        static fn(array $index): bool => ($index['Key_name'] ?? '') === 'unique_product_code' && (int)($index['Non_unique'] ?? 1) === 0
    )) > 0;
    if (!$hasUniqueProductCode) {
        $pdo->exec('ALTER TABLE products ADD UNIQUE KEY unique_product_code (code)');
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        image VARCHAR(255) NOT NULL,
        sort_order TINYINT UNSIGNED NOT NULL DEFAULT 1,
        UNIQUE KEY unique_product_image (product_id, image),
        INDEX idx_product_images_order (product_id, sort_order, id),
        CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("INSERT IGNORE INTO product_images (product_id, image, sort_order) SELECT id, image, 1 FROM products WHERE image <> ''");
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(12) NOT NULL DEFAULT '',
        user_id INT UNSIGNED NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(30) NOT NULL,
        status ENUM('Pendiente','Aceptado','En camino','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
        delivery_note VARCHAR(500) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at DATETIME NULL DEFAULT NULL,
        UNIQUE KEY unique_order_code (code),
        CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB");
    $orderColumns = $pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_COLUMN);
    $orderTableColumns = $pdo->query('SHOW INDEX FROM orders')->fetchAll(PDO::FETCH_ASSOC);
    $hasUniqueCode = count(array_filter(
        $orderTableColumns,
        static fn(array $index): bool => ($index['Key_name'] ?? '') === 'unique_order_code' && (int)($index['Non_unique'] ?? 1) === 0
    )) > 0;
    if (!in_array('code', $orderColumns, true)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN code VARCHAR(12) NOT NULL DEFAULT '' AFTER id");
    }
    if (!in_array('deleted_at', $orderColumns, true)) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at');
    }
    $ordersWithoutCode = $pdo->query('SELECT id FROM orders WHERE code = "" ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ordersWithoutCode as $orderId) {
        do {
            $code = 'GS-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $stmt = $pdo->prepare('UPDATE orders SET code = ? WHERE id = ? AND code = ""');
            try {
                $stmt->execute([$code, $orderId]);
                $updated = $stmt->rowCount() === 1;
                break;
            } catch (PDOException $exception) {
                if ((int)$exception->errorInfo[1] !== 1062) {
                    throw $exception;
                }
                $updated = false;
            }
        } while (!$updated);
    }
    if (!$hasUniqueCode) {
        $pdo->exec('ALTER TABLE orders ADD UNIQUE KEY unique_order_code (code)');
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NULL,
        product_code VARCHAR(20) NOT NULL DEFAULT '',
        product_name VARCHAR(150) NOT NULL,
        quantity INT UNSIGNED NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");
    $orderItemColumns = $pdo->query('SHOW COLUMNS FROM order_items')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('product_code', $orderItemColumns, true)) {
        $pdo->exec("ALTER TABLE order_items ADD COLUMN product_code VARCHAR(20) NOT NULL DEFAULT '' AFTER product_id");
    }
    $pdo->exec("UPDATE order_items i JOIN products p ON p.id = i.product_id SET i.product_code = p.code WHERE i.product_code = ''");
    $pdo->exec("UPDATE order_items SET product_code = CONCAT('PR-OLD-', LPAD(id, 6, '0')) WHERE product_code = ''");
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        rating TINYINT UNSIGNED NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY one_review_per_user_product (user_id, product_id),
        CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS review_votes (
        review_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        vote TINYINT NOT NULL,
        PRIMARY KEY (review_id, user_id),
        CONSTRAINT fk_votes_review FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
        CONSTRAINT fk_votes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_favorites (
        user_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, product_id),
        INDEX idx_user_favorites_product (product_id),
        CONSTRAINT fk_user_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_user_favorites_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS carousel_images (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255) NOT NULL,
        caption VARCHAR(180) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, full_name, email, phone, password_hash, role, language, theme) VALUES (?, ?, ?, ?, ?, 'admin', 'es', 'violeta')");
    $stmt->execute([ADMIN_USERNAME, 'Administrador GoStore', ADMIN_EMAIL, ADMIN_PHONE, password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT)]);
    $superAdminId = $pdo->query('SELECT user_id FROM superadmin_identity WHERE singleton_id = 1')->fetchColumn();
    if (!$superAdminId) {
        $superAdminId = $pdo->query("SELECT id FROM users WHERE role = 'superadmin' ORDER BY id LIMIT 1")->fetchColumn();
        if (!$superAdminId) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([SUPERADMIN_USERNAME, SUPERADMIN_EMAIL]);
            if ($stmt->fetchColumn()) {
                throw new RuntimeException('El nombre o correo reservado para SuperAdmin ya está en uso por otra cuenta.');
            }
            $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, password_hash, role, language, theme) VALUES (?, ?, ?, ?, 'superadmin', 'es', 'violeta')");
            $stmt->execute([SUPERADMIN_USERNAME, SUPERADMIN_USERNAME, SUPERADMIN_EMAIL, password_hash(SUPERADMIN_PASSWORD, PASSWORD_DEFAULT)]);
            $superAdminId = (int)$pdo->lastInsertId();
        }
        $stmt = $pdo->prepare('INSERT INTO superadmin_identity (singleton_id, user_id) VALUES (1, ?)');
        $stmt->execute([(int)$superAdminId]);
    }
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([(int)$superAdminId]);
    if ($stmt->fetchColumn() !== 'superadmin') {
        throw new RuntimeException('La cuenta única registrada para SuperAdmin no tiene el rol requerido.');
    }
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function go(string $url): never
{
    header('Location: ' . $url);
    exit;
}

const APP_LANGUAGES = ['es', 'en', 'fr', 'pt', 'it'];
const APP_THEMES = ['violeta', 'naranja', 'verde', 'azul', 'oscuro'];
const APP_CURRENCIES = ['PEN', 'USD', 'EUR'];
const CURRENCY_RATES = ['PEN' => 1.0, 'USD' => 1 / 3.70, 'EUR' => 1 / 4.00];
const CURRENCY_SYMBOLS = ['PEN' => 'S/', 'USD' => '$', 'EUR' => '€'];

function normalizeLanguage(string $value): string
{
    $value = strtolower(trim($value));
    return in_array($value, APP_LANGUAGES, true) ? $value : 'es';
}

function normalizeTheme(string $value): string
{
    $value = strtolower(trim($value));
    return in_array($value, APP_THEMES, true) ? $value : 'violeta';
}

function normalizeCurrency(string $value): string
{
    $value = strtoupper(trim($value));
    return in_array($value, APP_CURRENCIES, true) ? $value : 'PEN';
}

function convertCurrency(float $amountInPen, string $currency): float
{
    $currency = normalizeCurrency($currency);
    return $amountInPen * CURRENCY_RATES[$currency];
}

function formatMoney(float $amountInPen, string $currency): string
{
    $currency = normalizeCurrency($currency);
    $amount = convertCurrency($amountInPen, $currency);
    return CURRENCY_SYMBOLS[$currency] . ' ' . number_format($amount, 2, '.', ',');
}

function resolveUserAppearance(?array $user): array
{
    $preferredLanguage = normalizeLanguage((string)($user['language'] ?? 'en'));
    $preferredTheme = normalizeTheme((string)($user['theme'] ?? 'violeta'));
    $preferredCurrency = normalizeCurrency((string)($user['currency'] ?? 'PEN'));
    return ['language' => $preferredLanguage, 'theme' => $preferredTheme, 'currency' => $preferredCurrency];
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isSuperAdmin(): bool
{
    return (currentUser()['role'] ?? '') === 'superadmin';
}

function isAdmin(): bool
{
    return in_array((currentUser()['role'] ?? ''), ['admin', 'superadmin'], true);
}

function writeAuditLog(PDO $pdo, string $action, string $details = ''): void
{
    $actor = currentUser();
    $stmt = $pdo->prepare('INSERT INTO audit_log (actor_user_id, actor_username, actor_role, action, details) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        $actor ? (int)$actor['id'] : null,
        $actor['username'] ?? 'Sistema',
        $actor['role'] ?? 'system',
        $action,
        mb_substr($details, 0, 500),
    ]);
}

function requireOperationEnabled(PDO $pdo, string $permissionKey): void
{
    if (isSuperAdmin()) {
        return;
    }
    if (!array_key_exists($permissionKey, APP_PERMISSIONS)) {
        throw new LogicException('Permiso de operación no configurado: ' . $permissionKey);
    }
    $stmt = $pdo->prepare('SELECT enabled FROM app_permissions WHERE permission_key = ?');
    $stmt->execute([$permissionKey]);
    $enabled = $stmt->fetchColumn();
    if ($enabled === false) {
        throw new RuntimeException('No se pudo verificar el permiso de esta operación.');
    }
    if ((int)$enabled !== 1) {
        throw new RuntimeException('Esta operación está temporalmente restringida por el SuperAdmin.');
    }
}

function isOperationEnabled(PDO $pdo, string $permissionKey): bool
{
    if (!array_key_exists($permissionKey, APP_PERMISSIONS)) {
        throw new LogicException('Permiso de operación no configurado: ' . $permissionKey);
    }
    $stmt = $pdo->prepare('SELECT enabled FROM app_permissions WHERE permission_key = ?');
    $stmt->execute([$permissionKey]);
    $enabled = $stmt->fetchColumn();
    if ($enabled === false) {
        throw new RuntimeException('No se pudo verificar el permiso de esta operación.');
    }
    return (int)$enabled === 1;
}

function startUserSession(PDO $pdo, array $user): void
{
    $user['language'] = normalizeLanguage((string)($user['language'] ?? 'es'));
    $user['theme'] = normalizeTheme((string)($user['theme'] ?? 'violeta'));
    $user['currency'] = normalizeCurrency((string)($user['currency'] ?? 'PEN'));
    session_regenerate_id(true);
    $_SESSION['user'] = $user;
    $_SESSION['presentation_shown'] = false;
    if ($user['role'] === 'user') {
        $stmt = $pdo->prepare('SELECT 1 FROM user_guide_completions WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        if (!$stmt->fetchColumn()) {
            $_SESSION['show_user_guide'] = true;
        }
    }
}

function requireLogin(): void
{
    if (!currentUser()) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Inicia sesión para continuar.'];
        go('index.php?page=login');
    }
}

function requireCustomer(): void
{
    if (isAdmin()) {
        throw new RuntimeException('Las cuentas administradoras no pueden realizar compras.');
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        http_response_code(403);
        exit('Acceso denegado: esta sección es exclusiva del administrador.');
    }
}

function requireSuperAdmin(): void
{
    if (!isSuperAdmin()) {
        http_response_code(403);
        exit('Acceso denegado: esta sección es exclusiva del SuperAdmin.');
    }
}

function csrfField(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function verifyCsrf(): void
{
    if (!isset($_POST['csrf'], $_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$_POST['csrf'])) {
        http_response_code(419);
        exit('La sesión del formulario venció. Vuelve atrás y recarga la página.');
    }
}

function flash(string $type, string $text): void
{
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}

function getAdminEmail(PDO $pdo): string
{
    $stmt = $pdo->query("SELECT email FROM users WHERE role = 'admin' ORDER BY id LIMIT 1");
    $adminEmail = trim((string)$stmt->fetchColumn());
    if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        return $adminEmail;
    }
    $configuredEmail = trim(appSetting('GOSTORE_ADMIN_EMAIL'));
    return filter_var($configuredEmail, FILTER_VALIDATE_EMAIL) ? $configuredEmail : ADMIN_EMAIL;
}

function getAdminPhone(PDO $pdo): string
{
    $stmt = $pdo->query("SELECT phone FROM users WHERE role = 'admin' ORDER BY id LIMIT 1");
    $phone = preg_replace('/\D+/', '', (string)$stmt->fetchColumn()) ?? '';
    if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
        $phone = substr($phone, 1);
    }
    if (strlen($phone) === 9) {
        $phone = '51' . $phone;
    }
    return strlen($phone) >= 11 && strlen($phone) <= 15 ? $phone : '51' . ADMIN_PHONE;
}

function sendSmtpEmail(string $recipient, string $subject, string $body): void
{
    $host = trim(appSetting('GOSTORE_SMTP_HOST'));
    $username = appSetting('GOSTORE_SMTP_USERNAME');
    $password = appSetting('GOSTORE_SMTP_PASSWORD');
    $encryption = strtolower(trim(appSetting('GOSTORE_SMTP_ENCRYPTION', 'starttls')));
    $port = (int)appSetting('GOSTORE_SMTP_PORT', $encryption === 'ssl' ? '465' : '587');
    $from = trim(appSetting('GOSTORE_SMTP_FROM', $username));
    $recipient = trim($recipient);
    $missingSettings = [];
    foreach ([
        'GOSTORE_SMTP_HOST' => $host,
        'GOSTORE_SMTP_USERNAME' => $username,
        'GOSTORE_SMTP_PASSWORD' => $password,
    ] as $setting => $value) {
        if ($value === '') {
            $missingSettings[] = $setting;
        }
    }
    if ($missingSettings) {
        throw new RuntimeException('Falta configurar: ' . implode(', ', $missingSettings) . '. En XAMPP crea C:\\xampp\\gostore-config.php siguiendo README.md.');
    }
    if (preg_match('/[\r\n\/]/', $host) || !in_array($encryption, ['ssl', 'starttls'], true)
        || $port < 1 || $port > 65535 || !filter_var($from, FILTER_VALIDATE_EMAIL)
        || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('La configuración SMTP tiene valores inválidos. Revisa el host, puerto, cifrado y correo remitente.');
    }

    $transport = $encryption === 'ssl' ? 'ssl://' : 'tcp://';
    $socket = @stream_socket_client(
        $transport . $host . ':' . $port,
        $errorCode,
        $errorMessage,
        10,
        STREAM_CLIENT_CONNECT,
        stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]])
    );
    if ($socket === false) {
        error_log('GoStore SMTP connection failed: ' . $errorMessage . ' (' . $errorCode . ')');
        throw new RuntimeException('No se pudo conectar al servicio de correo. Inténtalo nuevamente.');
    }
    stream_set_timeout($socket, 10);

    $readResponse = static function (array $expectedCodes) use ($socket): string {
        $response = '';
        do {
            $line = fgets($socket, 515);
            if ($line === false) {
                throw new RuntimeException('El servidor de correo cerró la conexión.');
            }
            $response .= $line;
        } while (isset($line[3]) && $line[3] === '-');

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('El servidor de correo rechazó el mensaje (' . $code . ').');
        }
        return $response;
    };
    $writeAll = static function (string $data) use ($socket): void {
        $length = strlen($data);
        $offset = 0;
        while ($offset < $length) {
            $written = fwrite($socket, substr($data, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('No se pudo transmitir el correo al servidor.');
            }
            $offset += $written;
        }
    };
    $sendCommand = static function (string $command, array $expectedCodes) use ($writeAll, $readResponse): string {
        $writeAll($command . "\r\n");
        return $readResponse($expectedCodes);
    };

    try {
        $readResponse([220]);
        $sendCommand('EHLO gostore.local', [250]);
        if ($encryption === 'starttls') {
            $sendCommand('STARTTLS', [220]);
            if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                throw new RuntimeException('No se pudo establecer una conexión TLS segura con el correo.');
            }
            $sendCommand('EHLO gostore.local', [250]);
        }
        $sendCommand('AUTH LOGIN', [334]);
        $sendCommand(base64_encode($username), [334]);
        $sendCommand(base64_encode($password), [235]);
        $sendCommand('MAIL FROM:<' . $from . '>', [250]);
        $sendCommand('RCPT TO:<' . $recipient . '>', [250, 251]);
        $sendCommand('DATA', [354]);
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $message = 'From: GoStore <' . $from . ">\r\n"
            . 'To: <' . $recipient . ">\r\n"
            . 'Subject: ' . $encodedSubject . "\r\n"
            . 'MIME-Version: 1.0' . "\r\n"
            . 'Content-Type: text/plain; charset=UTF-8' . "\r\n"
            . 'Content-Transfer-Encoding: base64' . "\r\n\r\n"
            . chunk_split(base64_encode($body), 76, "\r\n")
            . "\r\n.\r\n";
        $writeAll($message);
        $readResponse([250]);
        $sendCommand('QUIT', [221]);
    } catch (RuntimeException $exception) {
        error_log('GoStore SMTP delivery failed: ' . $exception->getMessage());
        throw new RuntimeException('No se pudo enviar el correo. Inténtalo nuevamente.');
    } finally {
        fclose($socket);
    }
}

function sendAdminEmail(PDO $pdo, string $subject, string $body): void
{
    sendSmtpEmail(getAdminEmail($pdo), $subject, $body);
}

function createNotifications(PDO $pdo, array $recipientIds, string $type, string $message, string $link): void
{
    $stmt = $pdo->prepare('INSERT INTO notifications (recipient_user_id, type, message, link) VALUES (?, ?, ?, ?)');
    foreach (array_unique(array_map('intval', $recipientIds)) as $recipientId) {
        if ($recipientId > 0) {
            $stmt->execute([$recipientId, $type, $message, $link]);
        }
    }
}

function deleteCustomerAccount(PDO $pdo, int $userId, ?string $password = null): void
{
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT role, password_hash, username FROM users WHERE id = ? FOR UPDATE');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user || $user['role'] !== 'user') {
        throw new RuntimeException('La cuenta seleccionada no existe o no se puede eliminar.');
    }
    if ($password !== null && !password_verify($password, $user['password_hash'])) {
        throw new RuntimeException('La contraseña actual es incorrecta.');
    }
    $stmt = $pdo->prepare('SELECT id, code FROM orders WHERE user_id = ?');
    $stmt->execute([$userId]);
    foreach ($stmt->fetchAll() as $order) {
        writeAuditLog($pdo, 'order_deleted', 'Pedido ' . $order['code'] . ' eliminado junto con la cuenta ' . $user['username'] . '.');
    }
    writeAuditLog($pdo, 'user_account_deleted', 'Cuenta eliminada: ' . $user['username'] . ' (ID ' . $userId . ').');

    $stmt = $pdo->prepare('SELECT media_path FROM chat_messages WHERE sender_user_id = ? OR recipient_user_id = ?');
    $stmt->execute([$userId, $userId]);
    $chatMedia = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $pdo->prepare("DELETE n FROM notifications n INNER JOIN orders o ON n.type = 'order' AND n.message LIKE CONCAT('Nuevo pedido #', o.id, ' de %') WHERE o.user_id = ?");
    $stmt->execute([$userId]);
    $stmt = $pdo->prepare('DELETE i FROM order_items i INNER JOIN orders o ON o.id = i.order_id WHERE o.user_id = ?');
    $stmt->execute([$userId]);
    $stmt = $pdo->prepare('DELETE FROM orders WHERE user_id = ?');
    $stmt->execute([$userId]);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->execute([$userId]);
    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('No se pudo eliminar la cuenta seleccionada.');
    }
    $pdo->commit();
    deleteStoredChatMedia($chatMedia);
}

function deleteStoredChatMedia(array $mediaFiles): void
{
    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat-media';
    foreach (array_unique($mediaFiles) as $mediaFile) {
        if (!is_string($mediaFile) || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp|gif|mp4|webm|mov)$/', $mediaFile)) {
            continue;
        }
        $path = $directory . DIRECTORY_SEPARATOR . $mediaFile;
        if (is_file($path) && !unlink($path)) {
            error_log('GoStore could not remove chat attachment: ' . $mediaFile);
        }
    }
}

function storeProductImage(?array $file, string $old = ''): string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $old;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo cargar la imagen. Intenta con otro archivo.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('La imagen no puede superar los 5 MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Usa una imagen JPG, PNG o WEBP.');
    }

    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }
    if ($old !== '' && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $old)) {
        $oldPath = $directory . DIRECTORY_SEPARATOR . $old;
        if (is_file($oldPath)) {
            unlink($oldPath);
        }
    }
    return $filename;
}

function storeProductImages(?array $files, bool $required): array
{
    $uploads = [];
    if ($files && isset($files['name'], $files['error'], $files['size'], $files['tmp_name'])
        && is_array($files['name'])
        && is_array($files['error'])
        && is_array($files['size'])
        && is_array($files['tmp_name'])) {
        foreach ($files['name'] as $index => $name) {
            $error = (int)($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($error !== UPLOAD_ERR_OK) {
                throw new RuntimeException('No se pudieron cargar todas las imágenes. Intenta nuevamente.');
            }
            $uploads[] = [
                'name' => (string)$name,
                'size' => (int)($files['size'][$index] ?? 0),
                'tmp_name' => (string)($files['tmp_name'][$index] ?? ''),
            ];
        }
    }

    if (!$uploads && !$required) {
        return [];
    }
    if (count($uploads) < 2 || count($uploads) > 4) {
        throw new RuntimeException('Selecciona entre 2 y 4 imágenes para el producto.');
    }

    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    foreach ($uploads as &$upload) {
        if ($upload['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Cada imagen debe pesar como máximo 5 MB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
        if (!isset($extensions[$mime])) {
            throw new RuntimeException('Usa imágenes JPG, PNG o WEBP.');
        }
        $upload['extension'] = $extensions[$mime];
    }
    unset($upload);

    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
    }

    $storedImages = [];
    try {
        foreach ($uploads as $upload) {
            $filename = bin2hex(random_bytes(16)) . '.' . $upload['extension'];
            if (!move_uploaded_file($upload['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
                throw new RuntimeException('No se pudo guardar una de las imágenes.');
            }
            $storedImages[] = $filename;
        }
    } catch (Throwable $exception) {
        foreach ($storedImages as $filename) {
            $path = $directory . DIRECTORY_SEPARATOR . $filename;
            if (is_file($path) && !unlink($path)) {
                error_log('GoStore could not remove incomplete product image: ' . $filename);
            }
        }
        throw $exception;
    }

    return $storedImages;
}

function deleteStoredProductImages(array $images, ?PDO $pdo = null): void
{
    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    foreach (array_unique($images) as $image) {
        if (!is_string($image) || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $image)) {
            continue;
        }
        if ($pdo) {
            $referenceCheck = $pdo->prepare(
                'SELECT (SELECT COUNT(*) FROM products WHERE image = ?)'
                . ' + (SELECT COUNT(*) FROM product_images WHERE image = ?)'
                . ' + (SELECT COUNT(*) FROM carousel_images WHERE image = ?)'
            );
            $referenceCheck->execute([$image, $image, $image]);
            if ((int)$referenceCheck->fetchColumn() > 0) {
                continue;
            }
        }
        $path = $directory . DIRECTORY_SEPARATOR . $image;
        if (is_file($path) && !unlink($path)) {
            error_log('GoStore could not remove product image: ' . $image);
        }
    }
}

function getOrphanedStoredFiles(PDO $pdo): array
{
    $referencedProductFiles = array_fill_keys(array_filter(array_merge(
        $pdo->query("SELECT image FROM products WHERE image <> ''")->fetchAll(PDO::FETCH_COLUMN),
        $pdo->query("SELECT image FROM product_images WHERE image <> ''")->fetchAll(PDO::FETCH_COLUMN),
        $pdo->query("SELECT image FROM carousel_images WHERE image <> ''")->fetchAll(PDO::FETCH_COLUMN)
    ), 'is_string'), true);
    $referencedChatFiles = array_fill_keys(array_filter(
        $pdo->query("SELECT media_path FROM chat_messages WHERE media_path <> ''")->fetchAll(PDO::FETCH_COLUMN),
        'is_string'
    ), true);
    $result = [];
    $directories = [
        'product' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads',
        'chat' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat-media',
    ];
    foreach ($directories as $type => $directory) {
        if (!is_dir($directory)) {
            continue;
        }
        $referenced = $type === 'chat' ? $referencedChatFiles : $referencedProductFiles;
        foreach (scandir($directory) ?: [] as $filename) {
            $pattern = $type === 'chat'
                ? '/^[a-f0-9]{32}\.(jpg|png|webp|gif|mp4|webm|mov)$/'
                : '/^[a-f0-9]{32}\.(jpg|png|webp)$/';
            $path = $directory . DIRECTORY_SEPARATOR . $filename;
            if (preg_match($pattern, $filename) && is_file($path) && !is_link($path) && !isset($referenced[$filename])) {
                $result[] = ['type' => $type, 'filename' => $filename, 'path' => $path];
            }
        }
    }
    return $result;
}

function deleteOrphanedStoredFiles(PDO $pdo): int
{
    $orphans = getOrphanedStoredFiles($pdo);
    $deleted = 0;
    foreach ($orphans as $orphan) {
        if (!unlink($orphan['path'])) {
            throw new RuntimeException('No se pudo eliminar el archivo sobrante ' . $orphan['filename'] . '.');
        }
        $deleted++;
    }
    return $deleted;
}

$page = (string)($_GET['page'] ?? 'store');
$action = (string)($_POST['action'] ?? '');
$returnUrl = 'index.php?page=' . rawurlencode($page);
if ($action === 'product_favorite') {
    $candidateReturnUrl = (string)($_POST['return_url'] ?? '');
    if (preg_match('/\Aindex\.php(?:\?[A-Za-z0-9._~%=&+\-]*)?(?:#[A-Za-z0-9._~%=&+\-]*)?\z/D', $candidateReturnUrl)) {
        $returnUrl = $candidateReturnUrl;
    }
} elseif (in_array($action, ['review', 'vote', 'product_inquiry'], true)) {
    $returnProductId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
    if ($returnProductId !== false && $returnProductId > 0) {
        $returnUrl = 'index.php?product=' . $returnProductId . '#reviews';
    }
}
$orderWhatsapp = '';
$dbError = '';

try {
    $pdo = db();
} catch (PDOException $exception) {
    error_log('GoStore database initialization failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con MySQL. Inicia MySQL desde XAMPP y revisa la configuración de base de datos.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== '') {
    verifyCsrf();
    try {
        $actionPermissions = [
            'register' => 'user_register',
            'account_delete' => 'user_account_delete',
            'cart_add' => 'user_shopping',
            'product_favorite' => 'user_shopping',
            'cart_update' => 'user_shopping',
            'checkout' => 'user_checkout',
            'customer_order_cancel' => 'user_order_cancel',
            'customer_order_delete' => 'user_order_delete',
            'product_inquiry' => 'user_interaction',
            'review' => 'user_interaction',
            'vote' => 'user_interaction',
            'product_save' => 'admin_products',
            'product_delete' => 'admin_products',
            'carousel_save' => 'admin_carousel',
            'carousel_delete' => 'admin_carousel',
            'order_status' => 'admin_orders',
            'order_archive' => 'admin_orders',
            'admin_user_delete' => 'admin_users',
        ];
        if (isset($actionPermissions[$action])) {
            requireOperationEnabled($pdo, $actionPermissions[$action]);
        }
        switch ($action) {
            case 'register':
                $username = trim((string)($_POST['username'] ?? ''));
                $fullName = trim((string)($_POST['full_name'] ?? ''));
                $email = strtolower(trim((string)($_POST['email'] ?? '')));
                $password = (string)($_POST['password'] ?? '');
                if (mb_strlen($username) < 3 || mb_strlen($fullName) < 2 || strlen($email) > 190
                    || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
                    throw new RuntimeException('Revisa tus datos: ingresa un correo válido y una contraseña de al menos 8 caracteres.');
                }
                $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, password_hash, language, theme, currency) VALUES (?, ?, ?, ?, 'es', 'violeta', 'PEN')");
                $stmt->execute([$username, $fullName, $email, password_hash($password, PASSWORD_DEFAULT)]);
                $user = [
                    'id' => (int)$pdo->lastInsertId(),
                    'username' => $username,
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => '',
                    'address' => '',
                    'role' => 'user',
                    'language' => 'es',
                    'theme' => 'violeta',
                    'currency' => 'PEN',
                ];
                startUserSession($pdo, $user);
                writeAuditLog($pdo, 'user_registered', 'Nueva cuenta registrada: ' . $username . '.');
                flash('success', '¡Tu cuenta está lista! Revisa esta guía rápida para comenzar.');
                go('index.php');

            case 'login':
                $stmt = $pdo->prepare('SELECT id, username, full_name, email, phone, address, password_hash, role, language, theme, currency FROM users WHERE username = ? OR email = ? LIMIT 1');
                $identity = trim((string)($_POST['identity'] ?? ''));
                $stmt->execute([$identity, $identity]);
                $user = $stmt->fetch();
                if (!$user || !password_verify((string)($_POST['password'] ?? ''), $user['password_hash'])) {
                    throw new RuntimeException('Usuario/correo o contraseña incorrectos.');
                }
                unset($user['password_hash']);
                startUserSession($pdo, $user);
                writeAuditLog($pdo, 'login', 'Inicio de sesión correcto.');
                go($user['role'] === 'superadmin' ? 'index.php?page=superadmin' : ($user['role'] === 'admin' ? 'index.php?page=admin' : 'index.php'));

            case 'guide_show':
                requireLogin();
                requireCustomer();
                $_SESSION['show_user_guide'] = true;
                go('index.php');

            case 'guide_dismiss':
                requireLogin();
                requireCustomer();
                $stmt = $pdo->prepare('INSERT IGNORE INTO user_guide_completions (user_id) VALUES (?)');
                $stmt->execute([currentUser()['id']]);
                unset($_SESSION['show_user_guide']);
                go('index.php');

            case 'notification_read':
                requireLogin();
                $notificationId = filter_var($_POST['notification_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$notificationId) {
                    throw new RuntimeException('La notificación seleccionada no es válida.');
                }
                $stmt = $pdo->prepare('SELECT type, link FROM notifications WHERE id = ? AND recipient_user_id = ?');
                $stmt->execute([$notificationId, currentUser()['id']]);
                $notification = $stmt->fetch();
                if (!$notification) {
                    throw new RuntimeException('La notificación ya no existe.');
                }
                $stmt = $pdo->prepare('UPDATE notifications SET read_at = COALESCE(read_at, CURRENT_TIMESTAMP) WHERE id = ? AND recipient_user_id = ?');
                $stmt->execute([$notificationId, currentUser()['id']]);
                $destination = isAdmin()
                    ? 'index.php?page=admin-orders'
                    : (preg_match('/^index\.php\?product=[1-9][0-9]*$/', $notification['link']) ? $notification['link'] : 'index.php');
                go($destination);

            case 'logout':
                if (currentUser()) {
                    writeAuditLog($pdo, 'logout', 'Cierre de sesión.');
                    $stmt = $pdo->prepare('DELETE FROM chat_presence WHERE user_id = ?');
                    $stmt->execute([(int)currentUser()['id']]);
                }
                $_SESSION = [];
                if (ini_get('session.use_cookies')) {
                    $cookie = session_get_cookie_params();
                    setcookie(session_name(), '', [
                        'expires' => time() - 42000,
                        'path' => $cookie['path'],
                        'domain' => $cookie['domain'],
                        'secure' => $cookie['secure'],
                        'httponly' => $cookie['httponly'],
                        'samesite' => $cookie['samesite'] ?? 'Lax',
                    ]);
                }
                session_destroy();
                go('index.php');

            case 'account_delete':
                requireLogin();
                requireCustomer();
                $password = (string)($_POST['current_password'] ?? '');
                if ($password === '') {
                    throw new RuntimeException('Ingresa tu contraseña actual para eliminar la cuenta.');
                }
                deleteCustomerAccount($pdo, (int)currentUser()['id'], $password);
                $_SESSION = [];
                session_regenerate_id(true);
                flash('success', 'Tu cuenta y sus datos asociados fueron eliminados permanentemente.');
                go('index.php?page=login');

            case 'admin_user_delete':
                requireAdmin();
                $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
                if ($userId === false || $userId === null || $userId < 1) {
                    throw new RuntimeException('La cuenta seleccionada no es válida.');
                }
                deleteCustomerAccount($pdo, $userId);
                flash('success', 'La cuenta y sus datos asociados fueron eliminados permanentemente.');
                go('index.php?page=admin-users');

            case 'profile':
                requireLogin();
                $fullName = trim((string)($_POST['full_name'] ?? ''));
                $email = strtolower(trim((string)($_POST['email'] ?? '')));
                $phone = trim((string)($_POST['phone'] ?? ''));
                $address = trim((string)($_POST['address'] ?? ''));
                if ($fullName === '' || strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException('Ingresa un nombre y un correo válidos.');
                }
                $emailOwner = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
                $emailOwner->execute([$email, (int)currentUser()['id']]);
                if ($emailOwner->fetchColumn()) {
                    throw new RuntimeException('No pudimos guardar este correo porque ya está registrado en otra cuenta. Prueba con otro correo.');
                }
                $newPassword = (string)($_POST['new_password'] ?? '');
                if ($newPassword !== '' && strlen($newPassword) < 8) {
                    throw new RuntimeException('La nueva contraseña debe tener al menos 8 caracteres.');
                }
                $stmt = $pdo->prepare('UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
                try {
                    $stmt->execute([$fullName, $email, $phone, $address, currentUser()['id']]);
                } catch (PDOException $exception) {
                    if ((int)($exception->errorInfo[1] ?? 0) !== 1062) {
                        throw $exception;
                    }
                    throw new RuntimeException('No pudimos guardar este correo porque ya está registrado en otra cuenta. Prueba con otro correo.');
                }
                $_SESSION['user'] = array_merge(currentUser(), compact('fullName'));
                $_SESSION['user']['full_name'] = $fullName;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['user']['address'] = $address;
                if ($newPassword !== '') {
                    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), currentUser()['id']]);
                }
                writeAuditLog($pdo, 'profile_updated', 'Perfil actualizado por ' . currentUser()['username'] . '.');
                flash('success', 'Perfil actualizado correctamente.');
                go('index.php?page=profile');

            case 'superadmin_account':
                requireSuperAdmin();
                $username = trim((string)($_POST['username'] ?? ''));
                $newPassword = (string)($_POST['new_password'] ?? '');
                if (mb_strlen($username) < 3 || mb_strlen($username) > 80) {
                    throw new RuntimeException('El nombre de acceso debe tener entre 3 y 80 caracteres.');
                }
                if ($newPassword !== '' && strlen($newPassword) < 8) {
                    throw new RuntimeException('La nueva contraseña debe tener al menos 8 caracteres.');
                }
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ? WHERE id = ? AND role = 'superadmin'");
                $stmt->execute([$username, $username, (int)currentUser()['id']]);
                if ($stmt->rowCount() !== 1 && currentUser()['username'] !== $username) {
                    throw new RuntimeException('No se pudo actualizar la cuenta SuperAdmin.');
                }
                if ($newPassword !== '') {
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ? AND role = 'superadmin'");
                    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int)currentUser()['id']]);
                }
                writeAuditLog($pdo, 'superadmin_credentials_updated', 'Se actualizaron credenciales del SuperAdmin; el rol permanece protegido.');
                $pdo->commit();
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['full_name'] = $username;
                flash('success', 'Credenciales SuperAdmin actualizadas. El rol SuperAdmin no se modifica.');
                go('index.php?page=superadmin');

            case 'superadmin_permissions':
                requireSuperAdmin();
                $submittedPermissions = (array)($_POST['permissions'] ?? []);
                $stmt = $pdo->prepare('UPDATE app_permissions SET enabled = ? WHERE permission_key = ?');
                $pdo->beginTransaction();
                foreach (APP_PERMISSIONS as $permissionKey => $label) {
                    $stmt->execute([isset($submittedPermissions[$permissionKey]) ? 1 : 0, $permissionKey]);
                }
                writeAuditLog($pdo, 'permissions_updated', 'Se actualizaron las restricciones de operaciones.');
                $pdo->commit();
                flash('success', 'Permisos de operación actualizados.');
                go('index.php?page=superadmin#permissions');

            case 'superadmin_cleanup_media':
                requireSuperAdmin();
                $deletedFiles = deleteOrphanedStoredFiles($pdo);
                writeAuditLog($pdo, 'orphan_media_cleaned', 'Archivos multimedia sobrantes eliminados: ' . $deletedFiles . '.');
                flash('success', 'Limpieza completada: se eliminaron ' . $deletedFiles . ' archivos sin referencias.');
                go('index.php?page=superadmin#media-cleanup');

            case 'settings':
                requireLogin();
                $language = normalizeLanguage((string)($_POST['language'] ?? 'es'));
                $theme = normalizeTheme((string)($_POST['theme'] ?? 'violeta'));
                $currency = normalizeCurrency((string)($_POST['currency'] ?? 'PEN'));
                $stmt = $pdo->prepare('UPDATE users SET language = ?, theme = ?, currency = ? WHERE id = ?');
                $stmt->execute([$language, $theme, $currency, currentUser()['id']]);
                $_SESSION['user']['language'] = $language;
                $_SESSION['user']['theme'] = $theme;
                $_SESSION['user']['currency'] = $currency;
                writeAuditLog($pdo, 'account_settings_updated', 'Preferencias actualizadas por ' . currentUser()['username'] . '.');
                flash('success', 'Ajustes guardados correctamente.');
                go('index.php?page=settings');

            case 'cart_add':
                requireCustomer();
                $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
                $quantity = max(1, (int)($_POST['quantity'] ?? 1));
                $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                if (!$product || $product['stock'] < 1) {
                    throw new RuntimeException('Este producto no tiene stock disponible.');
                }
                $_SESSION['cart'] ??= [];
                $_SESSION['cart'][$productId] = min((int)$product['stock'], (int)($_SESSION['cart'][$productId] ?? 0) + $quantity);
                flash('success', 'Producto agregado al carrito.');
                go('index.php?page=cart');

            case 'product_favorite':
                requireLogin();
                requireCustomer();
                $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
                $favoriteAction = (string)($_POST['favorite_action'] ?? '');
                if (!$productId || !in_array($favoriteAction, ['add', 'remove'], true)) {
                    throw new RuntimeException('El producto seleccionado no es válido.');
                }
                $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ?');
                $stmt->execute([$productId]);
                if (!$stmt->fetchColumn()) {
                    throw new RuntimeException('El producto ya no está disponible.');
                }
                if ($favoriteAction === 'add') {
                    $stmt = $pdo->prepare('INSERT IGNORE INTO user_favorites (user_id, product_id) VALUES (?, ?)');
                    $stmt->execute([(int)currentUser()['id'], $productId]);
                    flash('success', 'Producto agregado a tus favoritos.');
                } else {
                    $stmt = $pdo->prepare('DELETE FROM user_favorites WHERE user_id = ? AND product_id = ?');
                    $stmt->execute([(int)currentUser()['id'], $productId]);
                    flash('success', 'Producto eliminado de tus favoritos.');
                }
                go($returnUrl);

            case 'cart_update':
                requireCustomer();
                $_SESSION['cart'] ??= [];
                foreach ((array)($_POST['quantity'] ?? []) as $id => $quantity) {
                    $id = filter_var($id, FILTER_VALIDATE_INT);
                    if (!$id) {
                        continue;
                    }
                    if ((int)$quantity < 1) {
                        unset($_SESSION['cart'][$id]);
                        continue;
                    }
                    $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
                    $stmt->execute([$id]);
                    $product = $stmt->fetch();
                    if ($product) {
                        $_SESSION['cart'][$id] = min((int)$quantity, (int)$product['stock']);
                    } else {
                        unset($_SESSION['cart'][$id]);
                    }
                }
                go('index.php?page=cart');

            case 'checkout':
                requireCustomer();
                requireLogin();
                $cart = $_SESSION['cart'] ?? [];
                if (!$cart) {
                    throw new RuntimeException('Tu carrito está vacío.');
                }
                $payment = (string)($_POST['payment_method'] ?? '');
                if (!in_array($payment, ['yape', 'plin', 'bbva', 'contra_entrega'], true)) {
                    throw new RuntimeException('Selecciona un medio de pago válido.');
                }
                $deliveryNote = trim((string)($_POST['delivery_note'] ?? ''));
                if ($deliveryNote === '') {
                    throw new RuntimeException('Indica la dirección donde se entregará tu pedido.');
                }
                $pdo->beginTransaction();
                $lines = [];
                $total = 0.0;
                foreach ($cart as $id => $quantity) {
                    $stmt = $pdo->prepare('SELECT id, code, name, price, stock FROM products WHERE id = ? FOR UPDATE');
                    $stmt->execute([(int)$id]);
                    $product = $stmt->fetch();
                    if (!$product || (int)$product['stock'] < (int)$quantity) {
                        throw new RuntimeException('El stock de un producto cambió. Actualiza tu carrito.');
                    }
                    $lines[] = [$product, (int)$quantity];
                    $total += (float)$product['price'] * (int)$quantity;
                }
                $user = currentUser();
                $orderCode = '';
                do {
                    $orderCode = 'GS-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
                    $stmt = $pdo->prepare("INSERT INTO orders (code, user_id, total, payment_method, delivery_note) VALUES (?, ?, ?, ?, ?)");
                    try {
                        $stmt->execute([$orderCode, $user['id'], $total, $payment, $deliveryNote]);
                        $inserted = true;
                    } catch (PDOException $exception) {
                        $inserted = false;
                        if ((int)$exception->errorInfo[1] !== 1062) {
                            throw $exception;
                        }
                    }
                } while (!$inserted);
                $orderId = (int)$pdo->lastInsertId();
                foreach ($lines as [$product, $quantity]) {
                    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_code, product_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$orderId, $product['id'], $product['code'], $product['name'], $quantity, $product['price']]);
                    $stmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
                    $stmt->execute([$quantity, $product['id']]);
                }
                $adminIds = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_COLUMN);
                createNotifications(
                    $pdo,
                    $adminIds,
                    'order',
                    'Nuevo pedido #' . $orderId . ' de ' . $user['full_name'] . ' por S/ ' . number_format($total, 2) . '.',
                    'index.php?page=admin-orders'
                );
                writeAuditLog($pdo, 'order_created', 'Pedido ' . $orderCode . ' creado por ' . $user['username'] . '. Total S/ ' . number_format($total, 2) . '.');
                $pdo->commit();
                $_SESSION['cart'] = [];
                $receiptLines = [
                    'BOLETA DE COMPRA · GOSTORE',
                    'Código único del pedido: ' . $orderCode,
                    'Cliente: ' . $user['full_name'],
                    'Correo: ' . $user['email'],
                    'Fecha: ' . date('d/m/Y H:i:s'),
                    'Pago: ' . strtoupper(str_replace('_', ' ', $payment)),
                    'Estado: Pendiente',
                    'Productos:',
                ];
                foreach ($lines as [$product, $quantity]) {
                    $receiptLines[] = (int)$quantity . ' x ' . $product['name'] . ' · Código de producto: ' . $product['code'] . ' · S/ ' . number_format((float)$product['price'] * $quantity, 2);
                }
                $receiptLines[] = 'Total: S/ ' . number_format($total, 2);
                $receiptText = implode("\n", $receiptLines);
                $adminEmail = getAdminEmail($pdo);
                $_SESSION['order_receipt'] = [
                    'code' => $orderCode,
                    'text' => $receiptText,
                    'whatsapp_url' => 'https://wa.me/' . getAdminPhone($pdo) . '?text=' . rawurlencode($receiptText),
                    'gmail_url' => 'https://mail.google.com/mail/?view=cm&fs=1&to=' . rawurlencode($adminEmail)
                        . '&su=' . rawurlencode('Boleta de compra ' . $orderCode)
                        . '&body=' . rawurlencode($receiptText),
                ];
                try {
                    sendAdminEmail($pdo, 'Nuevo pedido ' . $orderCode, $receiptText);
                    flash('success', 'Pedido confirmado. Código único: ' . $orderCode . '. La boleta también fue enviada al administrador.');
                } catch (RuntimeException $exception) {
                    error_log('GoStore order email could not be sent for ' . $orderCode . ': ' . $exception->getMessage());
                    flash('error', 'El pedido quedó registrado con el código ' . $orderCode . ', pero no se pudo enviar la boleta automáticamente. Usa WhatsApp o Gmail para compartirla.');
                }
                go('index.php?page=orders');

            case 'product_inquiry':
                requireCustomer();
                requireLogin();
                $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
                $message = trim((string)($_POST['message'] ?? ''));
                $inquiryChannel = (string)($_POST['inquiry_channel'] ?? '');
                if (!$productId || $message === '' || mb_strlen($message) > 2000
                    || !in_array($inquiryChannel, ['email', 'whatsapp'], true)) {
                    throw new RuntimeException('Escribe una consulta de hasta 2000 caracteres sobre el producto.');
                }
                $stmt = $pdo->prepare('SELECT code, name FROM products WHERE id = ?');
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                if (!$product) {
                    throw new RuntimeException('El producto consultado ya no está disponible.');
                }
                $user = currentUser();
                $inquiryBody = "Consulta de producto en GoStore\n"
                    . 'Canal elegido: ' . ($inquiryChannel === 'whatsapp' ? 'WhatsApp' : 'Correo') . "\n"
                    . 'Producto: ' . $product['name'] . "\n"
                    . 'Código de producto: ' . $product['code'] . "\n"
                    . 'Cliente: ' . $user['full_name'] . "\n"
                    . 'Correo del cliente: ' . $user['email'] . "\n\n"
                    . "Consulta:\n" . $message;
                $inquirySubject = 'Consulta ' . $product['code'] . ' · ' . $product['name'];
                try {
                    sendAdminEmail($pdo, $inquirySubject, $inquiryBody);
                } catch (RuntimeException $exception) {
                    error_log('GoStore inquiry email could not be sent: ' . $exception->getMessage());
                    if ($inquiryChannel === 'email') {
                        $gmailUrl = 'https://mail.google.com/mail/?view=cm&fs=1'
                            . '&to=' . rawurlencode(getAdminEmail($pdo))
                            . '&su=' . rawurlencode($inquirySubject)
                            . '&body=' . rawurlencode($inquiryBody);
                        go($gmailUrl);
                    }
                    flash('error', 'No se pudo enviar la consulta automáticamente por correo. WhatsApp está listo; pulsa Enviar en el chat para comunicarte con el administrador.');
                }
                if ($inquiryChannel === 'whatsapp') {
                    $whatsappMessage = 'Hola, quisiera consultar sobre ' . $product['name']
                        . ' (código ' . $product['code'] . "):\n\n"
                        . $message . "\n\n"
                        . "Mis datos de contacto:\n"
                        . 'Nombre: ' . $user['full_name'] . "\n"
                        . 'Correo electrónico: ' . $user['email'] . "\n\n"
                        . 'Gracias, quedo atento(a) a su respuesta.';
                    go('https://wa.me/' . getAdminPhone($pdo) . '?text=' . rawurlencode($whatsappMessage));
                }
                flash('success', 'Tu consulta sobre ' . $product['code'] . ' fue enviada al administrador.');
                go('index.php?product=' . $productId);

            case 'review':
                requireCustomer();
                requireLogin();
                $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
                $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
                $comment = trim((string)($_POST['comment'] ?? ''));
                if (!$productId || !$rating || $rating < 1 || $rating > 5 || $comment === '') {
                    throw new RuntimeException('Escribe un comentario y selecciona una calificación de 1 a 5 estrellas.');
                }
                $stmt = $pdo->prepare('INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = CURRENT_TIMESTAMP');
                $stmt->execute([currentUser()['id'], $productId, $rating, $comment]);
                flash('success', 'Tu reseña se publicó correctamente.');
                go('index.php?product=' . $productId . '#reviews');

            case 'vote':
                requireCustomer();
                requireLogin();
                $reviewId = filter_var($_POST['review_id'] ?? null, FILTER_VALIDATE_INT);
                $vote = filter_var($_POST['vote'] ?? null, FILTER_VALIDATE_INT);
                if (!$reviewId || !in_array($vote, [-1, 1], true)) {
                    throw new RuntimeException('Voto inválido.');
                }
                $stmt = $pdo->prepare('INSERT INTO review_votes (review_id, user_id, vote) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE vote = VALUES(vote)');
                $stmt->execute([$reviewId, currentUser()['id'], $vote]);
                go('index.php?product=' . (int)($_POST['product_id'] ?? 0) . '#reviews');

            case 'product_save':
                requireAdmin();
                $id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
                $isNewProduct = $id === 0;
                $name = trim((string)($_POST['name'] ?? ''));
                $category = trim((string)($_POST['category'] ?? ''));
                $description = trim((string)($_POST['description'] ?? ''));
                $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
                $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
                if ($name === '' || $category === '' || $description === '' || $price === false || $price <= 0 || $stock === false || $stock < 0) {
                    throw new RuntimeException('Completa correctamente todos los datos del producto.');
                }
                $oldImage = '';
                $oldImages = [];
                if ($id) {
                    $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
                    $stmt->execute([$id]);
                    $existing = $stmt->fetch();
                    if (!$existing) {
                        throw new RuntimeException('El producto que intentas editar ya no existe.');
                    }
                    $oldImage = $existing['image'];
                    $stmt = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order, id');
                    $stmt->execute([$id]);
                    $oldImages = array_column($stmt->fetchAll(), 'image');
                    if (!$oldImages && $oldImage !== '') {
                        $oldImages = [$oldImage];
                    }
                }
                $newImages = storeProductImages($_FILES['images'] ?? null, !$id);
                $image = $newImages[0] ?? $oldImage;
                try {
                    $pdo->beginTransaction();
                    if ($id) {
                        $stmt = $pdo->prepare('UPDATE products SET name = ?, category = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?');
                        $stmt->execute([$name, $category, $description, $price, $stock, $image, $id]);
                    } else {
                        $productCode = 'PR-' . strtoupper(bin2hex(random_bytes(8)));
                        $stmt = $pdo->prepare('INSERT INTO products (code, name, category, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?)');
                        $stmt->execute([$productCode, $name, $category, $description, $price, $stock, $image]);
                        $id = (int)$pdo->lastInsertId();
                    }
                    if ($newImages) {
                        $stmt = $pdo->prepare('DELETE FROM product_images WHERE product_id = ?');
                        $stmt->execute([$id]);
                        $stmt = $pdo->prepare('INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)');
                        foreach ($newImages as $index => $newImage) {
                            $stmt->execute([$id, $newImage, $index + 1]);
                        }
                    }
                    if ($isNewProduct) {
                        $customerIds = $pdo->query("SELECT id FROM users WHERE role = 'user'")->fetchAll(PDO::FETCH_COLUMN);
                        createNotifications(
                            $pdo,
                            $customerIds,
                            'product',
                            'Nuevo producto disponible: ' . $name . '.',
                            'index.php?product=' . $id
                        );
                    }
                    writeAuditLog($pdo, $isNewProduct ? 'product_published' : 'product_updated', 'Producto ' . $name . ' (ID ' . $id . ').');
                    $pdo->commit();
                } catch (Throwable $exception) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    deleteStoredProductImages($newImages, $pdo);
                    throw $exception;
                }
                if ($newImages && $oldImages) {
                    deleteStoredProductImages(array_diff($oldImages, $newImages), $pdo);
                }
                flash('success', $isNewProduct ? 'Producto publicado.' : 'Producto actualizado.');
                go('index.php?page=admin-products');

            case 'product_delete':
                requireAdmin();
                $id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
                $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
                $stmt->execute([$id]);
                $product = $stmt->fetch();
                if ($product) {
                    $stmt = $pdo->prepare('SELECT name FROM products WHERE id = ?');
                    $stmt->execute([$id]);
                    $productName = (string)$stmt->fetchColumn();
                    $stmt = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ?');
                    $stmt->execute([$id]);
                    $images = array_column($stmt->fetchAll(), 'image');
                    if ($product['image'] !== '') {
                        $images[] = $product['image'];
                    }
                    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
                    $stmt->execute([$id]);
                    writeAuditLog($pdo, 'product_deleted', 'Producto ' . $productName . ' (ID ' . $id . ') eliminado.');
                    deleteStoredProductImages($images, $pdo);
                }
                flash('success', 'Producto eliminado.');
                go('index.php?page=admin-products');

            case 'carousel_save':
                requireAdmin();
                $caption = trim((string)($_POST['caption'] ?? ''));
                if (mb_strlen($caption) > 180) {
                    throw new RuntimeException('El texto del carrusel no puede superar 180 caracteres.');
                }
                $image = storeProductImage($_FILES['image'] ?? null);
                if ($image === '') {
                    throw new RuntimeException('Selecciona una imagen para el carrusel.');
                }
                $stmt = $pdo->prepare('INSERT INTO carousel_images (image, caption) VALUES (?, ?)');
                $stmt->execute([$image, $caption]);
                writeAuditLog($pdo, 'carousel_image_published', 'Imagen agregada al carrusel.');
                flash('success', 'Imagen agregada al carrusel.');
                go('index.php?page=admin-carousel#carousel-form');

            case 'carousel_delete':
                requireAdmin();
                $id = filter_var($_POST['carousel_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$id) {
                    throw new RuntimeException('La imagen seleccionada no es válida.');
                }
                $stmt = $pdo->prepare('SELECT image FROM carousel_images WHERE id = ?');
                $stmt->execute([$id]);
                $carouselImage = $stmt->fetch();
                if (!$carouselImage) {
                    throw new RuntimeException('La imagen del carrusel ya no existe.');
                }
                $stmt = $pdo->prepare('DELETE FROM carousel_images WHERE id = ?');
                $stmt->execute([$id]);
                deleteStoredProductImages([$carouselImage['image']], $pdo);
                writeAuditLog($pdo, 'carousel_image_deleted', 'Imagen retirada del carrusel (ID ' . $id . ').');
                flash('success', 'Imagen retirada del carrusel.');
                go('index.php?page=admin-carousel#carousel-form');

            case 'order_status':
                requireAdmin();
                $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
                $status = (string)($_POST['status'] ?? '');
                if (!$orderId || !in_array($status, ['Aceptado', 'En camino', 'Entregado', 'Cancelado'], true)) {
                    throw new RuntimeException('No se pudo actualizar el estado del pedido.');
                }
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? AND deleted_at IS NULL FOR UPDATE');
                $stmt->execute([$orderId]);
                $oldStatus = $stmt->fetchColumn();
                if (!$oldStatus) {
                    throw new RuntimeException('El pedido ya no existe o fue retirado de la vista.');
                }
                if ($oldStatus === 'Cancelado') {
                    throw new RuntimeException('Un pedido cancelado no se puede reabrir.');
                }
                if ($status === 'Cancelado' && $oldStatus !== 'Cancelado') {
                    $stmt = $pdo->prepare('UPDATE products p INNER JOIN order_items i ON i.product_id = p.id SET p.stock = p.stock + i.quantity WHERE i.order_id = ?');
                    $stmt->execute([$orderId]);
                }
                $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
                $stmt->execute([$status, $orderId]);
                writeAuditLog($pdo, 'order_status_changed', 'Pedido #' . $orderId . ': ' . $oldStatus . ' -> ' . $status . '.');
                $pdo->commit();
                flash('success', 'Estado del pedido actualizado.');
                go('index.php?page=admin-orders#orders');

            case 'customer_order_cancel':
                requireCustomer();
                $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$orderId || $orderId < 1) {
                    throw new RuntimeException('El pedido seleccionado no es válido.');
                }
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? AND user_id = ? FOR UPDATE');
                $stmt->execute([$orderId, (int)currentUser()['id']]);
                $status = $stmt->fetchColumn();
                if ($status === false) {
                    throw new RuntimeException('No encontramos ese pedido en tu cuenta.');
                }
                if ($status !== 'Pendiente') {
                    throw new RuntimeException('Este pedido ya no se puede cancelar desde tu cuenta. Contacta al administrador para recibir ayuda.');
                }
                $stmt = $pdo->prepare('UPDATE products p INNER JOIN order_items i ON i.product_id = p.id SET p.stock = p.stock + i.quantity WHERE i.order_id = ?');
                $stmt->execute([$orderId]);
                $stmt = $pdo->prepare("UPDATE orders SET status = 'Cancelado' WHERE id = ? AND user_id = ? AND status = 'Pendiente'");
                $stmt->execute([$orderId, (int)currentUser()['id']]);
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('El pedido cambió de estado y no pudo cancelarse. Actualiza la página e inténtalo de nuevo.');
                }
                writeAuditLog($pdo, 'order_cancelled', 'Pedido #' . $orderId . ' cancelado por el cliente.');
                $pdo->commit();
                flash('success', 'Pedido cancelado. El stock quedó disponible nuevamente.');
                go('index.php?page=orders');

            case 'customer_order_delete':
                requireCustomer();
                $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$orderId || $orderId < 1) {
                    throw new RuntimeException('El pedido seleccionado no es válido.');
                }
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? AND user_id = ? FOR UPDATE');
                $stmt->execute([$orderId, (int)currentUser()['id']]);
                $status = $stmt->fetchColumn();
                if ($status === false) {
                    throw new RuntimeException('No encontramos ese pedido en tu cuenta.');
                }
                if (!in_array($status, ['Pendiente', 'Cancelado', 'Entregado'], true)) {
                    throw new RuntimeException('No se puede eliminar un pedido que está siendo preparado o enviado. Contacta al administrador para recibir ayuda.');
                }
                $stmt = $pdo->prepare('SELECT code FROM orders WHERE id = ?');
                $stmt->execute([$orderId]);
                $orderCode = (string)$stmt->fetchColumn();
                if ($status === 'Pendiente') {
                    $stmt = $pdo->prepare('UPDATE products p INNER JOIN order_items i ON i.product_id = p.id SET p.stock = p.stock + i.quantity WHERE i.order_id = ?');
                    $stmt->execute([$orderId]);
                }
                $stmt = $pdo->prepare("DELETE n FROM notifications n WHERE n.type = 'order' AND n.message LIKE CONCAT('Nuevo pedido #', ?, ' de %')");
                $stmt->execute([$orderId]);
                $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ? AND user_id = ?');
                $stmt->execute([$orderId, (int)currentUser()['id']]);
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('No se pudo eliminar el pedido. Actualiza la página e inténtalo de nuevo.');
                }
                writeAuditLog($pdo, 'order_deleted', 'Pedido ' . $orderCode . ' eliminado por el cliente (estado: ' . $status . ').');
                $pdo->commit();
                flash('success', 'El pedido y sus detalles se eliminaron permanentemente.');
                go('index.php?page=orders');

            case 'order_archive':
                requireAdmin();
                $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$orderId) {
                    throw new RuntimeException('El pedido seleccionado no es válido.');
                }
                $stmt = $pdo->prepare('UPDATE orders SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL');
                $stmt->execute([$orderId]);
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('El pedido ya no existe o ya fue retirado de la vista.');
                }
                writeAuditLog($pdo, 'order_archived', 'Pedido #' . $orderId . ' retirado de la vista administrativa.');
                flash('success', 'Pedido retirado de la vista. Se conservará en los reportes.');
                go('index.php?page=admin-orders#orders');

            default:
                throw new RuntimeException('Acción no reconocida.');
        }
    } catch (RuntimeException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', $exception->getMessage());
        go($returnUrl);
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('GoStore request failed: ' . $exception->getMessage());
        flash('error', $exception->getCode() === '23000' ? 'Ese usuario o correo ya está registrado.' : 'No se pudo completar la operación. Revisa los datos e inténtalo de nuevo.');
        go($returnUrl);
    }
}
