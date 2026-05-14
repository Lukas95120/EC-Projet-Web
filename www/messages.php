<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$pdo->query(
    'DELETE FROM private_messages
     WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)'
);

$userId = (int)$_SESSION['user']['id'];
$targetUserId = (int)($_GET['user_id'] ?? 0);

if ($targetUserId > 0 && $targetUserId !== $userId) {
    $stmt = $pdo->prepare(
        'SELECT id
         FROM friendships
         WHERE user_id = ?
         AND friend_id = ?
         LIMIT 1'
    );
    $stmt->execute([$userId, $targetUserId]);

    if (!$stmt->fetch()) {
        setFlash('error', 'Tu dois être ami avec cet utilisateur pour lui envoyer un message.');
        header('Location: friends.php');
        exit;
    }

    $userOneId = min($userId, $targetUserId);
    $userTwoId = max($userId, $targetUserId);

    $stmt = $pdo->prepare(
        'SELECT id
         FROM private_conversations
         WHERE user_one_id = ?
         AND user_two_id = ?
         LIMIT 1'
    );
    $stmt->execute([$userOneId, $userTwoId]);

    $conversation = $stmt->fetch();

    if ($conversation) {
        $conversationId = (int)$conversation['id'];
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO private_conversations
             (user_one_id, user_two_id)
             VALUES (?, ?)'
        );
        $stmt->execute([$userOneId, $userTwoId]);

        $conversationId = (int)$pdo->lastInsertId();
    }

    header('Location: conversation.php?id=' . $conversationId);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT
        private_conversations.*,

        CASE
            WHEN private_conversations.user_one_id = ? THEN user_two.id
            ELSE user_one.id
        END AS friend_id,

        CASE
            WHEN private_conversations.user_one_id = ? THEN user_two.username
            ELSE user_one.username
        END AS friend_username,

        CASE
            WHEN private_conversations.user_one_id = ? THEN user_two.avatar
            ELSE user_one.avatar
        END AS friend_avatar,

        (
            SELECT message
            FROM private_messages
            WHERE private_messages.conversation_id = private_conversations.id
            ORDER BY private_messages.created_at DESC
            LIMIT 1
        ) AS last_message,

        (
            SELECT created_at
            FROM private_messages
            WHERE private_messages.conversation_id = private_conversations.id
            ORDER BY private_messages.created_at DESC
            LIMIT 1
        ) AS last_message_at

     FROM private_conversations

     INNER JOIN users AS user_one
        ON private_conversations.user_one_id = user_one.id

     INNER JOIN users AS user_two
        ON private_conversations.user_two_id = user_two.id

     WHERE private_conversations.user_one_id = ?
     OR private_conversations.user_two_id = ?

     ORDER BY private_conversations.updated_at DESC'
);

$stmt->execute([
    $userId,
    $userId,
    $userId,
    $userId,
    $userId
]);

$conversations = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">

        <div class="admin-header">
            <div>
                <span class="eyebrow">Messages privés</span>
                <h1 class="section-title">Mes conversations</h1>
            </div>

            <a href="friends.php" class="btn btn-secondary">
                Mes amis
            </a>
        </div>

        <div class="dashboard-grid">
            <?php if (empty($conversations)): ?>

                <article class="info-box">
                    <h2>Aucune conversation</h2>
                    <p>Ajoute des amis pour commencer à discuter en privé.</p>
                    <a href="friends.php" class="btn">Voir mes amis</a>
                </article>

            <?php else: ?>

                <?php foreach ($conversations as $conversation): ?>
                    <article class="info-box">

                        <div class="mini-user-link">
                            <span class="mini-user-avatar">
                                <?php if (!empty($conversation['friend_avatar'])): ?>
                                    <img
                                        src="assets/uploads/<?= htmlspecialchars(basename($conversation['friend_avatar'])) ?>"
                                        alt="<?= htmlspecialchars($conversation['friend_username']) ?>"
                                    >
                                <?php else: ?>
                                    <?= htmlspecialchars(strtoupper(substr($conversation['friend_username'], 0, 1))) ?>
                                <?php endif; ?>
                            </span>

                            <strong>
                                <?= htmlspecialchars($conversation['friend_username']) ?>
                            </strong>
                        </div>

                        <p style="margin-top: 1rem;">
                            <?= !empty($conversation['last_message'])
                                ? htmlspecialchars($conversation['last_message'])
                                : 'Aucun message pour le moment.' ?>
                        </p>

                        <?php if (!empty($conversation['last_message_at'])): ?>
                            <p class="form-help">
                                Dernier message :
                                <?= htmlspecialchars(date('d/m/Y H:i', strtotime($conversation['last_message_at']))) ?>
                            </p>
                        <?php endif; ?>

                        <a
                            href="conversation.php?id=<?= htmlspecialchars($conversation['id']) ?>"
                            class="btn"
                        >
                            Ouvrir
                        </a>

                    </article>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>