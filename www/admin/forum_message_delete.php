<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

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

if ($messageId <= 0) {
    setFlash('error', 'Message invalide.');
    header('Location: forum_messages.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM forum_messages WHERE id = ?');
$stmt->execute([$messageId]);

setFlash('success', 'Message supprimé avec succès.');
header('Location: forum_messages.php');
exit;