<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: games.php');
    exit;
}

$gameId = (int)($_POST['game_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$userId = $_SESSION['user']['id'];

if ($gameId <= 0 || $rating < 1 || $rating > 5) {
    setFlash('error', 'Avis invalide.');
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM games WHERE id = ?');
$stmt->execute([$gameId]);

if (!$stmt->fetch()) {
    setFlash('error', 'Ce jeu n’existe pas.');
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id FROM reviews WHERE user_id = ? AND game_id = ?'
);
$stmt->execute([$userId, $gameId]);

if ($stmt->fetch()) {
    setFlash('error', 'Tu as déjà laissé un avis sur ce jeu.');
    header('Location: game.php?id=' . $gameId);
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO reviews (user_id, game_id, rating, comment)
     VALUES (?, ?, ?, ?)'
);
$stmt->execute([$userId, $gameId, $rating, $comment]);

setFlash('success', 'Avis ajouté avec succès.');

header('Location: game.php?id=' . $gameId);
exit;