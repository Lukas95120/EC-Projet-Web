<?php

function getUserBadges(array $user): array
{
    $badges = [];

    $reviewCount = (int)($user['review_count'] ?? 0);
    $favoriteCount = (int)($user['favorite_count'] ?? 0);
    $ticketCount = (int)($user['ticket_count'] ?? 0);
    $createdAt = $user['created_at'] ?? null;

    $accountAgeDays = 0;

    if ($createdAt) {
        $createdDate = new DateTime($createdAt);
        $now = new DateTime();
        $accountAgeDays = $createdDate->diff($now)->days;
    }

    $badges[] = [
        'key' => 'member',
        'label' => 'Membre',
        'icon' => '👤',
        'class' => 'badge-user'
    ];

    if ($accountAgeDays >= 30) {
        $badges[] = [
            'key' => 'veteran',
            'label' => 'Ancien membre',
            'icon' => '⏳',
            'class' => 'badge-veteran'
        ];
    }

    if ($reviewCount >= 1) {
        $badges[] = [
            'key' => 'reviewer_beginner',
            'label' => 'Critique débutant',
            'icon' => '⭐',
            'class' => 'badge-reviewer'
        ];
    }

    if ($reviewCount >= 5) {
        $badges[] = [
            'key' => 'reviewer_active',
            'label' => 'Critique actif',
            'icon' => '🏆',
            'class' => 'badge-expert'
        ];
    }

    if ($favoriteCount >= 5) {
        $badges[] = [
            'key' => 'collector',
            'label' => 'Collectionneur',
            'icon' => '❤️',
            'class' => 'badge-collector'
        ];
    }

    if ($ticketCount >= 1) {
        $badges[] = [
            'key' => 'contributor',
            'label' => 'Contributeur',
            'icon' => '🎫',
            'class' => 'badge-contributor'
        ];
    }

    return $badges;
}

function syncUserBadges(PDO $pdo, int $userId, array $badges): void
{
    require_once __DIR__ . '/notifications.php';

    foreach ($badges as $badge) {
        if (empty($badge['key'])) {
            continue;
        }

        $stmt = $pdo->prepare(
            'SELECT 1
             FROM user_badges
             WHERE user_id = ?
             AND badge_key = ?'
        );
        $stmt->execute([$userId, $badge['key']]);

        if ($stmt->fetch()) {
            continue;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO user_badges (user_id, badge_key)
             VALUES (?, ?)'
        );
        $stmt->execute([$userId, $badge['key']]);

        createNotification(
            $pdo,
            $userId,
            'badge',
            'Nouveau badge débloqué',
            'Tu as débloqué le badge "' . $badge['label'] . '".',
            'profile.php'
        );
    }
}