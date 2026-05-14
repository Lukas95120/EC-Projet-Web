<?php

function normalizeNotificationType(string $type): string
{
    $type = strtolower(trim($type));

    return match ($type) {
        'modération', 'moderation' => 'moderation',
        'badge', 'badges' => 'badge',
        'ticket', 'tickets' => 'ticket',
        'ami', 'amis', 'friend', 'friends' => 'friend',
        'message', 'messages', 'private_message' => 'message',
        'system', 'système', 'systeme' => 'system',
        default => 'system'
    };
}

function getNotificationMeta(string $type): array
{
    $type = normalizeNotificationType($type);

    return match ($type) {
        'moderation' => [
            'label' => 'Modération',
            'icon' => '🛡️',
            'class' => 'notification-type-moderation'
        ],
        'badge' => [
            'label' => 'Badge',
            'icon' => '🏆',
            'class' => 'notification-type-badge'
        ],
        'ticket' => [
            'label' => 'Ticket',
            'icon' => '🎫',
            'class' => 'notification-type-ticket'
        ],
        'friend' => [
            'label' => 'Ami',
            'icon' => '🤝',
            'class' => 'notification-type-friend'
        ],
        'message' => [
            'label' => 'Message privé',
            'icon' => '💬',
            'class' => 'notification-type-message'
        ],
        default => [
            'label' => 'Système',
            'icon' => '⚙️',
            'class' => 'notification-type-system'
        ]
    };
}

function createNotification(
    PDO $pdo,
    int $userId,
    string $type,
    string $title,
    string $message,
    ?string $link = null
): void {
    $type = normalizeNotificationType($type);

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