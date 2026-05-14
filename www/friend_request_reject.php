<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: index.php');
    exit;
}

$currentUserId = (int)$_SESSION['user']['id'];
$requestId = (int)($_POST['request_id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT *
     FROM friend_requests
     WHERE id = ?
     AND receiver_id = ?
     AND status = "pending"'
);
$stmt->execute([$requestId, $currentUserId]);
$request = $stmt->fetch();

if (!$request) {
    setFlash('error', 'Demande introuvable.');
    header('Location: friends.php');
    exit;
}

$stmt = $pdo->prepare(
    'UPDATE friend_requests
     SET status = "rejected",
         responded_at = NOW()
     WHERE id = ?'
);
$stmt->execute([$requestId]);

setFlash('success', 'Demande refusée.');

header('Location: user.php?id=' . (int)$request['sender_id']);
exit;