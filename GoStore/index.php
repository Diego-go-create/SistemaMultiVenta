<?php
declare(strict_types=1);

require __DIR__ . '/app.php';
require __DIR__ . '/app/Models/StoreModel.php';
require __DIR__ . '/app/Controllers/StoreController.php';
require __DIR__ . '/app/Controllers/ExportController.php';
require __DIR__ . '/app/Controllers/ChatController.php';

$store = new StoreModel($pdo);
if (isset($_GET['chat_media'])) {
    $messageId = filter_var($_GET['chat_media'], FILTER_VALIDATE_INT);
    if ($messageId === false || $messageId === null || $messageId < 1) {
        http_response_code(404);
        exit('Archivo no encontrado.');
    }
    (new ChatController($pdo))->serveMedia($messageId);
}
if (isset($_GET['chat'])) {
    (new ChatController($pdo))->handle();
}
if (isset($_GET['notifications'])) {
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, private');
    $user = currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Inicia sesión para consultar tus notificaciones.']);
        exit;
    }
    echo json_encode($store->getNotifications((int)$user['id']), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}
if (isset($_GET['export'])) {
    (new ExportController($store))->download();
}

$controller = new StoreController($store);
$controller->render();
