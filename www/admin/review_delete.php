<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/notifications.php';
require_once '../includes/admin_moderation.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reviews.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: reviews.php');
    exit;
}

$reviewId = (int)($_POST['review_id'] ?? 0);
$deleteReason = trim($_POST['delete_reason'] ?? '');

if ($reviewId <= 0) {
    setFlash('error', 'Avis invalide.');
    header('Location: reviews.php');
    exit;
}

if ($deleteReason === '') {
    setFlash('error', 'La raison de suppression est obligatoire.');
    header('Location: reviews.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT reviews.user_id, games.title AS game_title
     FROM reviews
     LEFT JOIN games ON reviews.game_id = games.id
     WHERE reviews.id = ?'
);

$stmt->execute([$reviewId]);

$review = $stmt->fetch();

if (!$review) {
    setFlash('error', 'Avis introuvable.');
    header('Location: reviews.php');
    exit;
}

$stmt = $pdo->prepare(
    'DELETE FROM reviews
     WHERE id = ?'
);

$stmt->execute([$reviewId]);

createNotification(
    $pdo,
    $review['user_id'],
    'Modération',
    'Avis supprimé',
    'Un administrateur a supprimé ton avis. Raison : ' . $deleteReason,
    'profile.php'
);

logAdminModerationAction(
    $pdo,
    $_SESSION['user']['id'] ?? null,
    (int)$review['user_id'],
    'delete_review',
    $deleteReason,
    'Avis supprimé sur le jeu : ' . ($review['game_title'] ?? 'Jeu inconnu')
);

setFlash('success', 'Avis supprimé avec succès.');

header('Location: reviews.php');
exit;