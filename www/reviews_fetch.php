<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$gameId = (int)($_GET['game_id'] ?? 0);

if ($gameId <= 0) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Jeu invalide.'
    ]);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT reviews.*, users.username, users.avatar
     FROM reviews
     INNER JOIN users ON reviews.user_id = users.id
     WHERE reviews.game_id = ?
     ORDER BY reviews.created_at DESC'
);
$stmt->execute([$gameId]);
$reviews = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT AVG(rating) AS average_rating, COUNT(*) AS review_count
     FROM reviews
     WHERE game_id = ?'
);
$stmt->execute([$gameId]);
$stats = $stmt->fetch();

$averageRating = $stats['average_rating']
    ? round($stats['average_rating'], 1)
    : null;

$reviewCount = (int)$stats['review_count'];

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

echo json_encode([
    'success' => true,
    'average_rating' => $averageRating,
    'review_count' => $reviewCount,
    'reviews' => array_map(function ($review) {
        $rating = (int)$review['rating'];

        return [
            'id' => (int)$review['id'],
            'user_id' => (int)$review['user_id'],
            'username' => $review['username'],
            'avatar' => $review['avatar'],
            'rating' => $rating,
            'comment' => nl2br(htmlspecialchars($review['comment'] ?? '')),
            'stars' => str_repeat('⭐', $rating) . str_repeat('☆', 5 - $rating),
            'created_at' => $review['created_at']
        ];
    }, $reviews)
]);

exit;