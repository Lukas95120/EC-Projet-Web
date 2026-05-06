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

$reviewId = (int)($_POST['review_id'] ?? 0);
$gameId = (int)($_POST['game_id'] ?? 0);
$userId = $_SESSION['user']['id'];

if ($reviewId <= 0 || $gameId <= 0) {
    if ($isAjax) {
        jsonResponse(false, 'Avis introuvable.');
    }

    setFlash('error', 'Avis introuvable.');
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare(
    'DELETE FROM reviews WHERE id = ? AND user_id = ?'
);
$stmt->execute([$reviewId, $userId]);

$stmt = $pdo->prepare(
    'SELECT AVG(rating) AS average_rating, COUNT(*) AS review_count
     FROM reviews
     WHERE game_id = ?'
);
$stmt->execute([$gameId]);
$ratingStats = $stmt->fetch();

$averageRating = $ratingStats['average_rating']
    ? round($ratingStats['average_rating'], 1)
    : null;

$reviewCount = (int)$ratingStats['review_count'];

if ($isAjax) {
    jsonResponse(true, 'Avis supprimé avec succès.', [
        'review_id' => $reviewId,
        'average_rating' => $averageRating,
        'review_count' => $reviewCount
    ]);
}

setFlash('success', 'Avis supprimé avec succès.');
header('Location: game.php?id=' . $gameId);
exit;