<?php

require_once __DIR__ . '/notifications.php';

function logModerationAction(
    PDO $pdo,
    int $userId,
    string $contentType,
    string $blockedContent,
    string $reason
): void {

    $stmt = $pdo->prepare(
        'INSERT INTO moderation_logs
         (user_id, content_type, blocked_content, reason)
         VALUES (?, ?, ?, ?)'
    );

    $stmt->execute([
        $userId,
        $contentType,
        $blockedContent,
        $reason
    ]);

    $stmt = $pdo->prepare(
        'SELECT username
         FROM users
         WHERE id = ?'
    );

    $stmt->execute([$userId]);

    $user = $stmt->fetch();

    $username = $user['username'] ?? 'Utilisateur inconnu';

    $stmt = $pdo->query(
        'SELECT id
         FROM users
         WHERE role = "admin"'
    );

    $admins = $stmt->fetchAll();

    foreach ($admins as $admin) {

        createNotification(
            $pdo,
            (int)$admin['id'],
            'moderation',
            'Contenu bloqué par la modération',
            $username . ' a tenté de publier un contenu bloqué (' . $contentType . ').',
            'admin/moderation_logs.php'
        );
    }
}