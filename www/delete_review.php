<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: games.php');
    exit;
}

$reviewId = (int)($_POST['review_id'] ?? 0);
$gameId = (int)($_POST['game_id'] ?? 0);
$userId = $_SESSION['user']['id'];

if ($reviewId <= 0 || $gameId <= 0) {
    setFlash('error', 'Avis introuvable.');
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare(
    'DELETE FROM reviews WHERE id = ? AND user_id = ?'
);
$stmt->execute([$reviewId, $userId]);

setFlash('success', 'Avis supprimé avec succès.');

header('Location: game.php?id=' . $gameId);
exit;