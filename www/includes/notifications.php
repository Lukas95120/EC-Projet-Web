<?php

function createNotification(
    PDO $pdo,
    int $userId,
    string $type,
    string $title,
    string $message,
    ?string $link = null
): void {
    $stmt = $pdo->prepare(
        'INSERT INTO notifications
         (user_id, type, title, message, link)
         VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $userId,
        $type,
        $title,
        $message,
        $link
    ]);
}

function getUnreadNotificationsCount(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM notifications
         WHERE user_id = ?
         AND is_read = 0'
    );

    $stmt->execute([$userId]);

    return (int)$stmt->fetchColumn();
}