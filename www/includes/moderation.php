<?php

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
}