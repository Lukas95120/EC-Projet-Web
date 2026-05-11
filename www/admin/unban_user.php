<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/notifications.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    die('Requête invalide.');
}

$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    setFlash('error', 'Utilisateur invalide.');
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, username
     FROM users
     WHERE id = ?'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'Utilisateur introuvable.');
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare(
    'UPDATE users
     SET is_banned = 0, ban_reason = NULL
     WHERE id = ?'
);
$stmt->execute([$userId]);

createNotification(
    $pdo,
    $userId,
    'moderation',
    'Compte débanni',
    'Ton compte a été réactivé. Tu peux à nouveau accéder au site.',
    'profile.php'
);

setFlash('success', 'Utilisateur débanni.');

header('Location: users.php');
exit;