<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/notifications.php';

requireLogin();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function jsonResponse(bool $success, string $message, array $extra = []): void
{
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        jsonResponse(false, 'Méthode non autorisée.');
    }

    header('Location: notifications.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    if ($isAjax) {
        jsonResponse(false, 'Requête invalide.');
    }

    setFlash('error', 'Requête invalide.');
    header('Location: notifications.php');
    exit;
}

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'DELETE FROM notifications
     WHERE user_id = ?'
);
$stmt->execute([$userId]);

if ($isAjax) {
    jsonResponse(true, 'Toutes les notifications ont été supprimées.', [
        'unread_count' => 0
    ]);
}

setFlash('success', 'Toutes les notifications ont été supprimées.');
header('Location: notifications.php');
exit;