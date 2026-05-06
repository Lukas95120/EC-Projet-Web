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

$stmt = $pdo->prepare(
    'DELETE FROM favorites WHERE user_id = ? AND game_id = ?'
);
$stmt->execute([$userId, $gameId]);

if ($isAjax) {
    jsonResponse(true, 'Jeu retiré des favoris.', [
        'is_favorite' => false,
        'button_text' => 'Ajouter aux favoris',
        'action' => 'add_favorite.php'
    ]);
}

setFlash('success', 'Jeu retiré des favoris.');
header('Location: ' . $return);
exit;