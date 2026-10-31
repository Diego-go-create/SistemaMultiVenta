<?php
declare(strict_types=1);

final class ChatController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function handle(): never
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, private');
        header('X-Content-Type-Options: nosniff');

        $user = currentUser();
        if (!$user) {
            $this->respond(['error' => 'Inicia sesión para usar el chat.'], 401);
        }

        try {
            if (!isSuperAdmin() && !isOperationEnabled($this->pdo, isAdmin() ? 'admin_chat' : 'user_chat')) {
                $this->respond(['error' => 'El acceso al chat está temporalmente restringido por el SuperAdmin.'], 403);
            }
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                session_write_close();
                $this->syncPresence((int)$user['id']);
                $this->respond($this->getState($user));
            }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->respond(['error' => 'Método no permitido.'], 405);
            }
            if (!isset($_POST['csrf'], $_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$_POST['csrf'])) {
                $this->respond(['error' => 'La sesión del chat venció. Recarga la página.'], 403);
            }
            $action = (string)($_POST['chat_action'] ?? '');
            if (isSuperAdmin() && in_array($action, ['typing', 'message', 'edit_message', 'delete_message'], true)) {
                $this->respond(['error' => 'El SuperAdmin solo puede supervisar las conversaciones.'], 403);
            }
            $peerId = $action === 'offline' ? 0 : $this->resolvePeerId($user, $_POST['peer_id'] ?? null);
            session_write_close();

            if ($action === 'typing') {
                $stmt = $this->pdo->prepare('INSERT INTO chat_presence (user_id, last_seen, typing_to_user_id, typing_at) VALUES (?, CURRENT_TIMESTAMP, ?, IF(?, CURRENT_TIMESTAMP, NULL)) ON DUPLICATE KEY UPDATE last_seen = VALUES(last_seen), typing_to_user_id = VALUES(typing_to_user_id), typing_at = VALUES(typing_at)');
                $isTyping = filter_var($_POST['typing'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $stmt->execute([(int)$user['id'], $isTyping ? $peerId : null, $isTyping]);
                $this->respond(['ok' => true]);
            }
            if ($action === 'message') {
                $this->sendMessage($user, $peerId);
            }
            if ($action === 'edit_message') {
                $this->editMessage($user);
            }
            if ($action === 'delete_message') {
                $this->deleteMessage($user);
            }
            if ($action === 'offline') {
                $stmt = $this->pdo->prepare('UPDATE chat_presence SET last_seen = CURRENT_TIMESTAMP - INTERVAL 60 SECOND, typing_to_user_id = NULL, typing_at = NULL WHERE user_id = ?');
                $stmt->execute([(int)$user['id']]);
                $this->respond(['ok' => true]);
            }
            $this->respond(['error' => 'Acción de chat no válida.'], 400);
        } catch (RuntimeException $exception) {
            $this->respond(['error' => $exception->getMessage()], 400);
        } catch (PDOException $exception) {
            error_log('GoStore chat request failed: ' . $exception->getMessage());
            $this->respond(['error' => 'No se pudo completar la operación del chat. Inténtalo nuevamente.'], 500);
        }
    }

    public function serveMedia(int $messageId): never
    {
        $user = currentUser();
        if (!$user) {
            http_response_code(401);
            exit('Inicia sesión para ver este archivo.');
        }
        if (!isSuperAdmin() && !isOperationEnabled($this->pdo, isAdmin() ? 'admin_chat' : 'user_chat')) {
            http_response_code(403);
            exit('El acceso al chat está temporalmente restringido por el SuperAdmin.');
        }
        session_write_close();
        if (isSuperAdmin()) {
            $stmt = $this->pdo->prepare("SELECT m.media_path, m.media_mime
                FROM chat_messages m
                JOIN users s ON s.id = m.sender_user_id
                JOIN users r ON r.id = m.recipient_user_id
                WHERE m.id = ? AND m.media_path <> ''
                  AND ((s.role = 'admin' AND r.role = 'user') OR (r.role = 'admin' AND s.role = 'user'))");
            $stmt->execute([$messageId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT media_path, media_mime FROM chat_messages WHERE id = ? AND media_path <> '' AND (sender_user_id = ? OR recipient_user_id = ?)");
            $stmt->execute([$messageId, (int)$user['id'], (int)$user['id']]);
        }
        $media = $stmt->fetch();
        if (!$media || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp|gif|mp4|webm|mov)$/', $media['media_path'])) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat-media' . DIRECTORY_SEPARATOR . $media['media_path'];
        if (!is_file($path)) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }

        header('Content-Type: ' . $media['media_mime']);
        header('Content-Length: ' . (string)filesize($path));
        header('Content-Disposition: inline; filename="chat-media"');
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    private function getState(array $user): array
    {
        $userId = (int)$user['id'];
        $isAdmin = isAdmin();
        $isSupervisor = isSuperAdmin();
        $conversationUserId = $isSupervisor ? $this->getAdminUserId() : $userId;
        $users = $isAdmin ? ($isSupervisor ? $this->getSuperAdminUserList((int)$conversationUserId) : $this->getAdminUserList($userId)) : [];
        if ($isAdmin && (!isset($_GET['peer_id']) || $_GET['peer_id'] === '')) {
            return ['peer' => null, 'admin_online' => true, 'typing' => false, 'messages' => [], 'users' => $users];
        }
        $peerId = $this->resolvePeerId($user, $_GET['peer_id'] ?? null);
        $peer = $this->getPeer($peerId);
        $conversationOwnerId = $isSupervisor ? (int)$conversationUserId : $userId;
        $typingStatement = $this->pdo->prepare('SELECT typing_at IS NOT NULL AND typing_to_user_id = ? AND typing_at >= CURRENT_TIMESTAMP - INTERVAL 8 SECOND FROM chat_presence WHERE user_id = ?');
        $typingStatement->execute([$conversationOwnerId, $peerId]);
        $typing = $isSupervisor ? false : (bool)$typingStatement->fetchColumn();

        $afterId = filter_var($_GET['after_id'] ?? 0, FILTER_VALIDATE_INT);
        if ($afterId === false || $afterId < 0) {
            throw new RuntimeException('La solicitud del chat no es válida.');
        }
        if (!$isSupervisor) {
            $this->pdo->prepare('UPDATE chat_messages SET read_at = CURRENT_TIMESTAMP WHERE sender_user_id = ? AND recipient_user_id = ? AND read_at IS NULL')
                ->execute([$peerId, $conversationOwnerId]);
        }

        if ($afterId === 0) {
            $stmt = $this->pdo->prepare('SELECT m.id, m.sender_user_id, m.message_type, m.message_text, m.created_at, m.read_at, u.username AS sender_name FROM chat_messages m JOIN users u ON u.id = m.sender_user_id WHERE ((m.sender_user_id = ? AND m.recipient_user_id = ?) OR (m.sender_user_id = ? AND m.recipient_user_id = ?)) ORDER BY m.id DESC LIMIT 100');
            $stmt->execute([$conversationOwnerId, $peerId, $peerId, $conversationOwnerId]);
            $messages = array_reverse($stmt->fetchAll());
        } else {
            $stmt = $this->pdo->prepare('SELECT m.id, m.sender_user_id, m.message_type, m.message_text, m.created_at, m.read_at, u.username AS sender_name FROM chat_messages m JOIN users u ON u.id = m.sender_user_id WHERE ((m.sender_user_id = ? AND m.recipient_user_id = ?) OR (m.sender_user_id = ? AND m.recipient_user_id = ?)) AND m.id > ? ORDER BY m.id ASC LIMIT 100');
            $stmt->execute([$conversationOwnerId, $peerId, $peerId, $conversationOwnerId, $afterId]);
            $messages = $stmt->fetchAll();
        }
        foreach ($messages as &$message) {
            $message['id'] = (int)$message['id'];
            $message['sender_user_id'] = (int)$message['sender_user_id'];
            $message['media_url'] = $message['message_type'] === 'text' ? '' : 'index.php?chat_media=' . $message['id'];
        }
        unset($message);

        return [
            'peer' => $peer,
            'admin_online' => $isAdmin ? true : $this->isOnline($peerId),
            'typing' => $typing,
            'messages' => $messages,
            'users' => $users,
        ];
    }

    private function resolvePeerId(array $user, mixed $requestedPeer): int
    {
        if (in_array($user['role'], ['admin', 'superadmin'], true)) {
            $peerId = filter_var($requestedPeer, FILTER_VALIDATE_INT);
            if ($peerId === false || $peerId === null || $peerId < 1) {
                throw new RuntimeException('Selecciona un usuario para abrir el chat.');
            }
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'user'");
            $stmt->execute([$peerId]);
            if (!$stmt->fetchColumn()) {
                throw new RuntimeException('El usuario seleccionado no está disponible.');
            }
            return $peerId;
        }

        $adminId = $this->pdo->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1")->fetchColumn();
        if (!$adminId) {
            throw new RuntimeException('El chat con el administrador no está disponible.');
        }
        return (int)$adminId;
    }

    private function syncPresence(int $userId): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO chat_presence (user_id, last_seen) VALUES (?, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE last_seen = VALUES(last_seen), typing_to_user_id = IF(typing_at IS NULL OR typing_at < CURRENT_TIMESTAMP - INTERVAL 8 SECOND, NULL, typing_to_user_id), typing_at = IF(typing_at IS NULL OR typing_at < CURRENT_TIMESTAMP - INTERVAL 8 SECOND, NULL, typing_at)');
        $stmt->execute([$userId]);
    }

    private function getAdminUserId(): int
    {
        $adminId = $this->pdo->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1")->fetchColumn();
        if (!$adminId) {
            throw new RuntimeException('No hay un administrador disponible para supervisar el chat.');
        }
        return (int)$adminId;
    }

    private function getSuperAdminUserList(int $adminId): array
    {
        $stmt = $this->pdo->prepare("SELECT u.id, u.username, u.full_name,
            (SELECT o.code FROM orders o WHERE o.user_id = u.id AND o.deleted_at IS NULL ORDER BY o.id DESC LIMIT 1) AS latest_order_code,
            (SELECT o.status FROM orders o WHERE o.user_id = u.id AND o.deleted_at IS NULL ORDER BY o.id DESC LIMIT 1) AS latest_order_status,
            (p.last_seen >= CURRENT_TIMESTAMP - INTERVAL 30 SECOND) AS online,
            0 AS unread_count,
            (SELECT MAX(m.created_at) FROM chat_messages m WHERE (m.sender_user_id = u.id AND m.recipient_user_id = ?) OR (m.sender_user_id = ? AND m.recipient_user_id = u.id)) AS last_message_at
            FROM users u LEFT JOIN chat_presence p ON p.user_id = u.id
            WHERE u.role = 'user' AND EXISTS (
                SELECT 1 FROM chat_messages m
                WHERE (m.sender_user_id = u.id AND m.recipient_user_id = ?)
                   OR (m.sender_user_id = ? AND m.recipient_user_id = u.id)
            ) ORDER BY last_message_at DESC, u.username ASC LIMIT 100");
        $stmt->execute([$adminId, $adminId, $adminId, $adminId]);
        $users = $stmt->fetchAll();
        foreach ($users as &$listedUser) {
            $listedUser['id'] = (int)$listedUser['id'];
            $listedUser['online'] = (bool)$listedUser['online'];
            $listedUser['unread_count'] = 0;
        }
        unset($listedUser);
        return $users;
    }

    private function getAdminUserList(int $adminId): array
    {
        $stmt = $this->pdo->prepare("SELECT u.id, u.username, u.full_name,
            (SELECT o.code FROM orders o WHERE o.user_id = u.id AND o.deleted_at IS NULL ORDER BY o.id DESC LIMIT 1) AS latest_order_code,
            (SELECT o.status FROM orders o WHERE o.user_id = u.id AND o.deleted_at IS NULL ORDER BY o.id DESC LIMIT 1) AS latest_order_status,
            (p.last_seen >= CURRENT_TIMESTAMP - INTERVAL 30 SECOND) AS online,
            (SELECT COUNT(*) FROM chat_messages m WHERE m.sender_user_id = u.id AND m.recipient_user_id = ? AND m.read_at IS NULL) AS unread_count,
            (SELECT MAX(m.created_at) FROM chat_messages m WHERE (m.sender_user_id = u.id AND m.recipient_user_id = ?) OR (m.sender_user_id = ? AND m.recipient_user_id = u.id)) AS last_message_at
            FROM users u LEFT JOIN chat_presence p ON p.user_id = u.id
            WHERE u.role = 'user' AND ((p.last_seen >= CURRENT_TIMESTAMP - INTERVAL 30 SECOND)
                OR EXISTS (SELECT 1 FROM chat_messages m WHERE (m.sender_user_id = u.id AND m.recipient_user_id = ?) OR (m.sender_user_id = ? AND m.recipient_user_id = u.id)))
            ORDER BY online DESC, last_message_at DESC, u.username ASC LIMIT 100");
        $stmt->execute([$adminId, $adminId, $adminId, $adminId, $adminId]);
        $users = $stmt->fetchAll();
        foreach ($users as &$listedUser) {
            $listedUser['id'] = (int)$listedUser['id'];
            $listedUser['online'] = (bool)$listedUser['online'];
            $listedUser['unread_count'] = (int)$listedUser['unread_count'];
        }
        unset($listedUser);
        return $users;
    }

    private function getPeer(int $peerId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, full_name FROM users WHERE id = ?');
        $stmt->execute([$peerId]);
        $peer = $stmt->fetch();
        if (!$peer) {
            throw new RuntimeException('La conversación seleccionada ya no está disponible.');
        }
        $peer['id'] = (int)$peer['id'];
        return $peer;
    }

    private function isOnline(int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT last_seen >= CURRENT_TIMESTAMP - INTERVAL 30 SECOND FROM chat_presence WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (bool)$stmt->fetchColumn();
    }

    private function sendMessage(array $user, int $peerId): never
    {
        $text = trim((string)($_POST['message_text'] ?? ''));
        if (!mb_check_encoding($text, 'UTF-8') || mb_strlen($text) > 3000) {
            throw new RuntimeException('El mensaje no puede superar los 3000 caracteres y debe usar texto válido.');
        }

        $attachment = $_FILES['attachment'] ?? null;
        $mediaPath = '';
        $mediaMime = '';
        $messageType = 'text';
        if ($attachment && (int)($attachment['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ((int)$attachment['error'] !== UPLOAD_ERR_OK || (int)$attachment['size'] > 20 * 1024 * 1024) {
                throw new RuntimeException('El archivo no se pudo cargar o supera el límite de 20 MB.');
            }
            if (!is_uploaded_file((string)$attachment['tmp_name'])) {
                throw new RuntimeException('El archivo adjunto no es válido.');
            }
            $extensions = [
                'image/jpeg' => ['jpg', 'image'],
                'image/png' => ['png', 'image'],
                'image/webp' => ['webp', 'image'],
                'image/gif' => ['gif', 'image'],
                'video/mp4' => ['mp4', 'video'],
                'video/webm' => ['webm', 'video'],
                'video/quicktime' => ['mov', 'video'],
            ];
            $mediaMime = (new finfo(FILEINFO_MIME_TYPE))->file((string)$attachment['tmp_name']);
            if (!isset($extensions[$mediaMime])) {
                throw new RuntimeException('Adjunta una imagen JPG, PNG, WEBP o GIF, o un video MP4, WEBM o MOV.');
            }
            [$extension, $mediaKind] = $extensions[$mediaMime];
            $messageType = $mediaKind;
            $mediaPath = bin2hex(random_bytes(16)) . '.' . $extension;
            $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat-media';
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException('No se pudo preparar el almacenamiento de archivos del chat.');
            }
            if (!move_uploaded_file((string)$attachment['tmp_name'], $directory . DIRECTORY_SEPARATOR . $mediaPath)) {
                throw new RuntimeException('No se pudo guardar el archivo adjunto.');
            }
        }
        if ($text === '' && $mediaPath === '') {
            throw new RuntimeException('Escribe un mensaje o adjunta una imagen o video.');
        }

        try {
            $stmt = $this->pdo->prepare('INSERT INTO chat_messages (sender_user_id, recipient_user_id, message_type, message_text, media_path, media_mime) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([(int)$user['id'], $peerId, $messageType, $text, $mediaPath, $mediaMime]);
        } catch (PDOException $exception) {
            if ($mediaPath !== '') {
                deleteStoredChatMedia([$mediaPath]);
            }
            throw $exception;
        }
        writeAuditLog($this->pdo, 'chat_message_sent', 'Mensaje enviado a la cuenta ID ' . $peerId . '.');
        $this->pdo->prepare('UPDATE chat_presence SET typing_to_user_id = NULL, typing_at = NULL WHERE user_id = ?')->execute([(int)$user['id']]);
        $this->respond(['ok' => true, 'message_id' => (int)$this->pdo->lastInsertId()]);
    }

    private function editMessage(array $user): never
    {
        $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT);
        if ($messageId === false || $messageId === null || $messageId < 1) {
            throw new RuntimeException('La referencia del mensaje es inválida.');
        }

        $text = trim((string)($_POST['message_text'] ?? ''));
        if ($text === '') {
            throw new RuntimeException('Escribe un texto para editar el mensaje.');
        }
        if (!mb_check_encoding($text, 'UTF-8') || mb_strlen($text) > 3000) {
            throw new RuntimeException('El mensaje no puede superar los 3000 caracteres y debe usar texto válido.');
        }

        $stmt = $this->pdo->prepare('SELECT sender_user_id, message_type FROM chat_messages WHERE id = ?');
        $stmt->execute([$messageId]);
        $message = $stmt->fetch();
        if (!$message) {
            throw new RuntimeException('El mensaje ya no existe.');
        }
        if ((int)$message['sender_user_id'] !== (int)$user['id']) {
            throw new RuntimeException('Solo puedes editar tus propios mensajes.');
        }
        if ((string)$message['message_type'] !== 'text') {
            throw new RuntimeException('Solo puedes editar mensajes de texto.');
        }

        $update = $this->pdo->prepare('UPDATE chat_messages SET message_text = ? WHERE id = ? AND sender_user_id = ?');
        $update->execute([$text, $messageId, (int)$user['id']]);
        if ($update->rowCount() === 0) {
            throw new RuntimeException('No se pudo actualizar el mensaje.');
        }

        writeAuditLog($this->pdo, 'chat_message_edited', 'Mensaje #' . $messageId . ' editado.');
        $this->respond(['ok' => true]);
    }

    private function deleteMessage(array $user): never
    {
        $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT);
        if ($messageId === false || $messageId === null || $messageId < 1) {
            throw new RuntimeException('La referencia del mensaje es inválida.');
        }

        $stmt = $this->pdo->prepare('SELECT sender_user_id, media_path FROM chat_messages WHERE id = ?');
        $stmt->execute([$messageId]);
        $message = $stmt->fetch();
        if (!$message) {
            throw new RuntimeException('El mensaje ya no existe.');
        }
        if ((int)$message['sender_user_id'] !== (int)$user['id']) {
            throw new RuntimeException('Solo puedes eliminar tus propios mensajes.');
        }

        $mediaPath = (string)($message['media_path'] ?? '');
        if ($mediaPath !== '') {
            $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat-media' . DIRECTORY_SEPARATOR . $mediaPath;
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $delete = $this->pdo->prepare('DELETE FROM chat_messages WHERE id = ? AND sender_user_id = ?');
        $delete->execute([$messageId, (int)$user['id']]);
        if ($delete->rowCount() === 0) {
            throw new RuntimeException('No se pudo eliminar el mensaje.');
        }

        writeAuditLog($this->pdo, 'chat_message_deleted', 'Mensaje #' . $messageId . ' eliminado.');
        $this->respond(['ok' => true]);
    }

    private function respond(array $data, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }
}
