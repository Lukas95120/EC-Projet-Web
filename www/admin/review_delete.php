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

if ($reviewId <= 0) {
    setFlash('error', 'Avis invalide.');
    header('Location: reviews.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
$stmt->execute([$reviewId]);

setFlash('success', 'Avis supprimé avec succès.');
header('Location: reviews.php');
exit;