<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/notifications.php';

requireLogin();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function notificationJsonResponse(bool $success, string $message, array $extra = []): void
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
        notificationJsonResponse(false, 'Méthode non autorisée.');
    }

    header('Location: notifications.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    if ($isAjax) {
        notificationJsonResponse(false, 'Requête invalide.');
    }

    setFlash('error', 'Requête invalide.');
    header('Location: notifications.php');
    exit;
}

$id = (int)($_POST['notification_id'] ?? 0);
$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'DELETE FROM notifications
     WHERE id = ? AND user_id = ?'
);
$stmt->execute([$id, $userId]);

$unreadCount = getUnreadNotificationsCount($pdo, $userId);

if ($isAjax) {
    notificationJsonResponse(true, 'Notification supprimée.', [
        'unread_count' => $unreadCount
    ]);
}

setFlash('success', 'Notification supprimée.');
header('Location: notifications.php');
exit;