<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/content_filter.php';
require_once 'includes/moderation.php';

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
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$userId = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'] ?? 'Utilisateur';

$stmt = $pdo->prepare('SELECT avatar FROM users WHERE id = ?');
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

$userAvatar = $currentUser['avatar'] ?? null;

if ($gameId <= 0 || $rating < 1 || $rating > 5) {
    if ($isAjax) {
        jsonResponse(false, 'Avis invalide.');
    }

    setFlash('error', 'Avis invalide.');
    header('Location: games.php');
    exit;
}

$contentError = $comment !== '' ? validateUserContent($comment) : null;

if ($contentError !== null) {
    logModerationAction($pdo, $userId, 'review', $comment, $contentError);

    if ($isAjax) {
        jsonResponse(false, $contentError, [
            'clear_content' => true
        ]);
    }

    setFlash('error', $contentError);
    header('Location: game.php?id=' . $gameId);
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
    'SELECT id FROM reviews WHERE user_id = ? AND game_id = ?'
);
$stmt->execute([$userId, $gameId]);

if ($stmt->fetch()) {
    if ($isAjax) {
        jsonResponse(false, 'Tu as déjà laissé un avis sur ce jeu.');
    }

    setFlash('error', 'Tu as déjà laissé un avis sur ce jeu.');
    header('Location: game.php?id=' . $gameId);
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO reviews (user_id, game_id, rating, comment)
     VALUES (?, ?, ?, ?)'
);
$stmt->execute([$userId, $gameId, $rating, $comment]);

$reviewId = (int)$pdo->lastInsertId();

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
    jsonResponse(true, 'Avis ajouté avec succès.', [
        'review' => [
            'id' => $reviewId,
            'user_id' => $userId,
            'username' => $username,
            'avatar' => $userAvatar,
            'rating' => $rating,
            'comment' => nl2br(htmlspecialchars($comment)),
            'stars' => str_repeat('⭐', $rating) . str_repeat('☆', 5 - $rating)
        ],
        'average_rating' => $averageRating,
        'review_count' => $reviewCount
    ]);
}

setFlash('success', 'Avis ajouté avec succès.');
header('Location: game.php?id=' . $gameId);
exit;