<?php

function logAdminModerationAction(
    PDO $pdo,
    ?int $adminId,
    ?int $targetUserId,
    string $action,
    ?string $reason = null,
    ?string $details = null
): void {
    $stmt = $pdo->prepare(
        'INSERT INTO admin_moderation_logs
         (admin_id, target_user_id, action, reason, details)
         VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $adminId,
        $targetUserId,
        $action,
        $reason,
        $details
    ]);
}