<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$pdo->query('DELETE FROM forum_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 HOUR)');

$stmt = $pdo->query(
    'SELECT forum_messages.*, users.username, users.avatar
     FROM forum_messages
     INNER JOIN users ON forum_messages.user_id = users.id
     ORDER BY forum_messages.created_at DESC'
);

$messages = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'messages' => array_map(function ($message) {
        return [
            'id' => (int)$message['id'],
            'user_id' => (int)$message['user_id'],
            'username' => $message['username'],
            'avatar' => $message['avatar'],
            'message' => nl2br(htmlspecialchars($message['message'])),
            'created_at' => $message['created_at']
        ];
    }, $messages)
]);

exit;