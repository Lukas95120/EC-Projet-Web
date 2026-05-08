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
        'label' => 'Membre',
        'icon' => '👤',
        'class' => 'badge-user'
    ];

    if ($accountAgeDays >= 30) {
        $badges[] = [
            'label' => 'Ancien membre',
            'icon' => '⏳',
            'class' => 'badge-veteran'
        ];
    }

    if ($reviewCount >= 1) {
        $badges[] = [
            'label' => 'Critique débutant',
            'icon' => '⭐',
            'class' => 'badge-reviewer'
        ];
    }

    if ($reviewCount >= 5) {
        $badges[] = [
            'label' => 'Critique actif',
            'icon' => '🏆',
            'class' => 'badge-expert'
        ];
    }

    if ($favoriteCount >= 5) {
        $badges[] = [
            'label' => 'Collectionneur',
            'icon' => '❤️',
            'class' => 'badge-collector'
        ];
    }

    if ($ticketCount >= 1) {
        $badges[] = [
            'label' => 'Contributeur',
            'icon' => '🎫',
            'class' => 'badge-contributor'
        ];
    }

    return $badges;
}