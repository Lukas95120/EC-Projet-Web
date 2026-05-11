<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/notifications.php';

requireLogin();

$userId = $_SESSION['user']['id'];
$count = getUnreadNotificationsCount($pdo, $userId);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

echo json_encode([
    'success' => true,
    'count' => $count
]);

exit;