<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/notifications.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: index.php');
    exit;
}

$currentUserId = (int)$_SESSION['user']['id'];
$requestId = (int)($_POST['request_id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT *
     FROM friend_requests
     WHERE id = ?
     AND receiver_id = ?
     AND status = "pending"'
);
$stmt->execute([$requestId, $currentUserId]);
$request = $stmt->fetch();

if (!$request) {
    setFlash('error', 'Demande introuvable.');
    header('Location: friends.php');
    exit;
}

$senderId = (int)$request['sender_id'];

$pdo->beginTransaction();

$stmt = $pdo->prepare(
    'UPDATE friend_requests
     SET status = "accepted",
         responded_at = NOW()
     WHERE id = ?'
);
$stmt->execute([$requestId]);

$stmt = $pdo->prepare(
    'INSERT IGNORE INTO friendships (user_id, friend_id)
     VALUES (?, ?), (?, ?)'
);
$stmt->execute([
    $currentUserId,
    $senderId,
    $senderId,
    $currentUserId
]);

$pdo->commit();

createNotification(
    $pdo,
    $senderId,
    'friend',
    'Demande d’ami acceptée',
    $_SESSION['user']['username'] . ' a accepté ta demande d’ami.',
    'user.php?id=' . $currentUserId
);

setFlash('success', 'Demande acceptée.');

header('Location: user.php?id=' . $senderId);
exit;