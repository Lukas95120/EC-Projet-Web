<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

header('Content-Type: application/json');

$pdo->query(
    'DELETE FROM private_messages
     WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)'
);

$userId = (int)$_SESSION['user']['id'];
$conversationId = (int)($_GET['conversation_id'] ?? 0);

if ($conversationId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Conversation invalide.'
    ]);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT *
     FROM private_conversations
     WHERE id = ?
     AND (
        user_one_id = ?
        OR user_two_id = ?
     )
     LIMIT 1'
);

$stmt->execute([
    $conversationId,
    $userId,
    $userId
]);

$conversation = $stmt->fetch();

if (!$conversation) {
    echo json_encode([
        'success' => false,
        'message' => 'Conversation introuvable.'
    ]);
    exit;
}

$stmt = $pdo->prepare(
    'UPDATE private_messages
     SET is_read = 1
     WHERE conversation_id = ?
     AND sender_id != ?'
);

$stmt->execute([
    $conversationId,
    $userId
]);

$stmt = $pdo->prepare(
    'SELECT
        private_messages.*,
        users.username,
        users.avatar
     FROM private_messages
     INNER JOIN users
        ON private_messages.sender_id = users.id
     WHERE private_messages.conversation_id = ?
     ORDER BY private_messages.created_at ASC'
);

$stmt->execute([$conversationId]);
$messages = $stmt->fetchAll();

$formattedMessages = [];

foreach ($messages as $message) {
    $formattedMessages[] = [
        'id' => (int)$message['id'],
        'sender_id' => (int)$message['sender_id'],
        'username' => $message['username'],
        'avatar' => $message['avatar'],
        'message' => nl2br(htmlspecialchars($message['message'])),
        'created_at' => date('d/m/Y H:i', strtotime($message['created_at'])),
        'is_mine' => (int)$message['sender_id'] === $userId ? 1 : 0
    ];
}

echo json_encode([
    'success' => true,
    'messages' => $formattedMessages
]);

exit;