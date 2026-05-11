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
$banReason = trim($_POST['ban_reason'] ?? '');

if ($userId <= 0) {
    setFlash('error', 'Utilisateur invalide.');
    header('Location: users.php');
    exit;
}

if ($banReason === '') {
    $banReason = 'Aucune raison précisée.';
}

$stmt = $pdo->prepare(
    'SELECT id, username, role
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

if ($user['role'] === 'admin') {
    setFlash('error', 'Impossible de bannir un administrateur.');
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare(
    'UPDATE users
     SET is_banned = 1, ban_reason = ?
     WHERE id = ?'
);
$stmt->execute([$banReason, $userId]);

createNotification(
    $pdo,
    $userId,
    'moderation',
    'Compte banni',
    'Ton compte a été suspendu par un administrateur. Raison : ' . $banReason,
    'profile.php'
);

setFlash('success', 'Utilisateur banni.');

header('Location: users.php');
exit;