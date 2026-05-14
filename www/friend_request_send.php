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

$senderId = (int)$_SESSION['user']['id'];
$receiverId = (int)($_POST['receiver_id'] ?? 0);

if ($receiverId <= 0 || $receiverId === $senderId) {
    setFlash('error', 'Demande invalide.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username FROM users WHERE id = ?');
$stmt->execute([$receiverId]);
$receiver = $stmt->fetch();

if (!$receiver) {
    setFlash('error', 'Utilisateur introuvable.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id
     FROM friendships
     WHERE user_id = ?
     AND friend_id = ?'
);
$stmt->execute([$senderId, $receiverId]);

if ($stmt->fetch()) {
    setFlash('error', 'Vous êtes déjà amis.');
    header('Location: user.php?id=' . $receiverId);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id
     FROM friend_requests
     WHERE sender_id = ?
     AND receiver_id = ?
     AND status = "pending"'
);
$stmt->execute([$senderId, $receiverId]);

if ($stmt->fetch()) {
    setFlash('error', 'Demande déjà envoyée.');
    header('Location: user.php?id=' . $receiverId);
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO friend_requests (sender_id, receiver_id)
     VALUES (?, ?)'
);
$stmt->execute([$senderId, $receiverId]);

createNotification(
    $pdo,
    $receiverId,
    'friend',
    'Nouvelle demande d’ami',
    $_SESSION['user']['username'] . ' souhaite t’ajouter en ami.',
    'user.php?id=' . $senderId
);

setFlash('success', 'Demande d’ami envoyée.');

header('Location: user.php?id=' . $receiverId);
exit;