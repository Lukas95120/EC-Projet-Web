<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/admin_moderation.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Méthode non autorisée.');
    header('Location: dashboard.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: dashboard.php');
    exit;
}

$gameId = (int)($_POST['game_id'] ?? 0);

if ($gameId <= 0) {
    setFlash('error', 'Jeu invalide.');
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, title
     FROM games
     WHERE id = ?'
);
$stmt->execute([$gameId]);

$game = $stmt->fetch();

if (!$game) {
    setFlash('error', 'Jeu introuvable.');
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare(
    'DELETE FROM games
     WHERE id = ?'
);
$stmt->execute([$gameId]);

logAdminModerationAction(
    $pdo,
    $_SESSION['user']['id'] ?? null,
    null,
    'delete_game',
    'Suppression de jeu',
    'Jeu supprimé du catalogue : ' . $game['title']
);

setFlash('success', 'Le jeu "' . $game['title'] . '" a été supprimé avec succès.');

header('Location: dashboard.php');
exit;