<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/notifications.php';
require_once '../includes/admin_moderation.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: users.php');
    exit;
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
     SET is_banned = 0,
         ban_reason = NULL
     WHERE id = ?'
);

$stmt->execute([$userId]);

createNotification(
    $pdo,
    $userId,
    'Modération',
    'Compte débanni',
    'Ton compte a été réactivé. Tu peux à nouveau accéder au site.',
    'profile.php'
);

logAdminModerationAction(
    $pdo,
    $_SESSION['user']['id'] ?? null,
    $userId,
    'unban_user',
    null,
    'Utilisateur débanni depuis la gestion des membres.'
);

setFlash('success', 'Utilisateur débanni avec succès.');

header('Location: users.php');
exit;