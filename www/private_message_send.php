<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: messages.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: messages.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$conversationId = (int)($_POST['conversation_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($conversationId <= 0 || $message === '') {
    setFlash('error', 'Message invalide.');
    header('Location: messages.php');
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
    setFlash('error', 'Conversation introuvable.');
    header('Location: messages.php');
    exit;
}

$receiverId = (int)$conversation['user_one_id'] === $userId
    ? (int)$conversation['user_two_id']
    : (int)$conversation['user_one_id'];

$stmt = $pdo->prepare(
    'INSERT INTO private_messages
     (conversation_id, sender_id, message)
     VALUES (?, ?, ?)'
);
$stmt->execute([
    $conversationId,
    $userId,
    $message
]);

$stmt = $pdo->prepare(
    'UPDATE private_conversations
     SET updated_at = NOW()
     WHERE id = ?'
);
$stmt->execute([$conversationId]);

if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
) {
    echo json_encode([
        'success' => true,
        'message' => 'Message envoyé.'
    ]);
    exit;
}

setFlash('success', 'Message envoyé.');

header('Location: conversation.php?id=' . $conversationId);
exit;