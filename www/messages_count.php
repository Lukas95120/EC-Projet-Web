<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {

    echo json_encode([
        'success' => false
    ]);

    exit;
}

$userId = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT COUNT(*)
     FROM private_messages
     INNER JOIN private_conversations
        ON private_messages.conversation_id = private_conversations.id
     WHERE private_messages.sender_id != ?
     AND private_messages.is_read = 0
     AND (
        private_conversations.user_one_id = ?
        OR private_conversations.user_two_id = ?
     )'
);

$stmt->execute([
    $userId,
    $userId,
    $userId
]);

$count = (int)$stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'count' => $count
]);