<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

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
    'SELECT user_id
     FROM reviews
     WHERE id = ?'
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

$stmt = $pdo->prepare(
    'INSERT INTO notifications
     (user_id, type, title, message)
     VALUES (?, ?, ?, ?)'
);

$stmt->execute([
    $review['user_id'],
    'Modération',
    'Avis supprimé',
    'Un administrateur a supprimé ton avis. Raison : ' . $deleteReason
]);

setFlash('success', 'Avis supprimé avec succès.');

header('Location: reviews.php');
exit;