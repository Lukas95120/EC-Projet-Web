<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/notifications.php';
require_once '../includes/admin_moderation.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forum_messages.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: forum_messages.php');
    exit;
}

$messageId = (int)($_POST['message_id'] ?? 0);
$deleteReason = trim($_POST['delete_reason'] ?? '');

if ($messageId <= 0) {
    setFlash('error', 'Message invalide.');
    header('Location: forum_messages.php');
    exit;
}

if ($deleteReason === '') {
    setFlash('error', 'La raison de suppression est obligatoire.');
    header('Location: forum_messages.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT user_id
     FROM forum_messages
     WHERE id = ?'
);

$stmt->execute([$messageId]);

$message = $stmt->fetch();

if (!$message) {
    setFlash('error', 'Message introuvable.');
    header('Location: forum_messages.php');
    exit;
}

$stmt = $pdo->prepare(
    'DELETE FROM forum_messages
     WHERE id = ?'
);

$stmt->execute([$messageId]);

createNotification(
    $pdo,
    $message['user_id'],
    'Modération',
    'Message supprimé',
    'Un administrateur a supprimé ton message forum. Raison : ' . $deleteReason,
    'profile.php'
);

logAdminModerationAction(
    $pdo,
    $_SESSION['user']['id'] ?? null,
    (int)$message['user_id'],
    'delete_forum_message',
    $deleteReason,
    'Message forum supprimé par un administrateur.'
);

setFlash('success', 'Message supprimé avec succès.');

header('Location: forum_messages.php');
exit;