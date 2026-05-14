<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/notifications.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: games.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: games.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$gameId = (int)($_POST['game_id'] ?? 0);
$friendId = (int)($_POST['friend_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($gameId <= 0 || $friendId <= 0 || $friendId === $userId) {
    setFlash('error', 'Partage invalide.');
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, title
     FROM games
     WHERE id = ?
     LIMIT 1'
);
$stmt->execute([$gameId]);
$game = $stmt->fetch();

if (!$game) {
    setFlash('error', 'Jeu introuvable.');
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id
     FROM friendships
     WHERE user_id = ?
     AND friend_id = ?
     LIMIT 1'
);
$stmt->execute([$userId, $friendId]);

if (!$stmt->fetch()) {
    setFlash('error', 'Tu peux seulement partager un jeu avec un ami.');
    header('Location: game.php?id=' . $gameId);
    exit;
}

$notificationMessage = $_SESSION['user']['username']
    . ' te recommande le jeu "' . $game['title'] . '".';

if ($message !== '') {
    $notificationMessage .= ' Message : ' . $message;
}

createNotification(
    $pdo,
    $friendId,
    'friend',
    'Jeu recommandé',
    $notificationMessage,
    'game.php?id=' . $gameId
);

setFlash('success', 'Jeu partagé avec ton ami.');

header('Location: game.php?id=' . $gameId);
exit;