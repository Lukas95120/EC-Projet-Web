<?php

require_once __DIR__ . '/badges.php';

function syncCurrentUserBadges(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return;
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM favorites WHERE user_id = ?'
    );
    $stmt->execute([$userId]);
    $favoriteCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM reviews WHERE user_id = ?'
    );
    $stmt->execute([$userId]);
    $reviewCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM tickets WHERE user_id = ?'
    );
    $stmt->execute([$userId]);
    $ticketCount = (int)$stmt->fetchColumn();

    $user['favorite_count'] = $favoriteCount;
    $user['review_count'] = $reviewCount;
    $user['ticket_count'] = $ticketCount;

    $badges = getUserBadges($user);

    syncUserBadges($pdo, $userId, $badges);
}