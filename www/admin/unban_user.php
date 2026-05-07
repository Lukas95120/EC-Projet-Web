<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    die('Requête invalide.');
}

$userId = (int)($_POST['user_id'] ?? 0);

$stmt = $pdo->prepare(
    'UPDATE users
     SET is_banned = 0
     WHERE id = ?'
);

$stmt->execute([$userId]);

setFlash('success', 'Utilisateur débanni.');

header('Location: users.php');
exit;