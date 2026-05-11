<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

header('Content-Type: application/json; charset=utf-8');

function jsonResponse(bool $success, string $message, array $extra = []): void
{
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));

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
    'UPDATE notifications
     SET is_read = 1
     WHERE user_id = ?'
);

$stmt->execute([$userId]);

if ($isAjax) {
    jsonResponse(true, 'Toutes les notifications ont été marquées comme lues.', [
        'unread_count' => 0
    ]);
}

setFlash('success', 'Toutes les notifications ont été marquées comme lues.');
header('Location: notifications.php');
exit;