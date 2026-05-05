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
$return = $_POST['return'] ?? 'favorites.php';
$userId = $_SESSION['user']['id'];

if ($gameId <= 0) {
    setFlash('error', 'Jeu introuvable.');
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare(
    'DELETE FROM favorites WHERE user_id = ? AND game_id = ?'
);
$stmt->execute([$userId, $gameId]);

setFlash('success', 'Jeu retiré des favoris.');

header('Location: ' . $return);
exit;