<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function jsonResponse(bool $success, string $message, array $extra = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    if ($isAjax) {
        jsonResponse(false, 'Requête invalide.');
    }

    setFlash('error', 'Requête invalide.');
    header('Location: games.php');
    exit;
}

$gameId = (int)($_POST['game_id'] ?? 0);
$return = $_POST['return'] ?? 'favorites.php';
$userId = $_SESSION['user']['id'];

if ($gameId <= 0) {
    if ($isAjax) {
        jsonResponse(false, 'Jeu introuvable.');
    }

    setFlash('error', 'Jeu introuvable.');
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM games WHERE id = ?');
$stmt->execute([$gameId]);

if (!$stmt->fetch()) {
    if ($isAjax) {
        jsonResponse(false, 'Ce jeu n’existe pas.');
    }

    setFlash('error', 'Ce jeu n’existe pas.');
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare(
    'INSERT IGNORE INTO favorites (user_id, game_id)
     VALUES (?, ?)'
);
$stmt->execute([$userId, $gameId]);

if ($isAjax) {
    jsonResponse(true, 'Jeu ajouté aux favoris.', [
        'is_favorite' => true,
        'button_text' => 'Retirer des favoris',
        'action' => 'remove_favorite.php'
    ]);
}

setFlash('success', 'Jeu ajouté aux favoris.');
header('Location: ' . $return);
exit;