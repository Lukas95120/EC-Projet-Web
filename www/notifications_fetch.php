<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT *
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC'
);

$stmt->execute([$userId]);

$notifications = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'notifications' => $notifications
]);