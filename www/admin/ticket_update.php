<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/notifications.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Méthode non autorisée.');
    header('Location: tickets.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Requête invalide.');
    header('Location: tickets.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

$allowedStatuses = ['pending', 'accepted', 'rejected'];

if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
    setFlash('error', 'Ticket invalide.');
    header('Location: tickets.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT user_id, title
     FROM tickets
     WHERE id = ?'
);
$stmt->execute([$id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    setFlash('error', 'Ticket introuvable.');
    header('Location: tickets.php');
    exit;
}

$addedToCatalog = 0;

$stmt = $pdo->prepare(
    'UPDATE tickets
     SET status = ?, added_to_catalog = ?
     WHERE id = ?'
);
$stmt->execute([$status, $addedToCatalog, $id]);

if ($status === 'accepted') {
    createNotification(
        $pdo,
        (int)$ticket['user_id'],
        'ticket',
        'Ticket accepté',
        'Ton ticket pour le jeu "' . $ticket['title'] . '" a été accepté.',
        'tickets.php'
    );

    setFlash('success', 'Ticket accepté. Il est maintenant dans les jeux à ajouter.');
    header('Location: tickets_to_add.php');
    exit;
}

if ($status === 'rejected') {
    createNotification(
        $pdo,
        (int)$ticket['user_id'],
        'ticket',
        'Ticket refusé',
        'Ton ticket pour le jeu "' . $ticket['title'] . '" a été refusé.',
        'tickets.php'
    );

    setFlash('success', 'Ticket refusé et archivé.');
    header('Location: tickets_archived.php');
    exit;
}

setFlash('success', 'Statut du ticket mis à jour.');
header('Location: tickets.php');
exit;