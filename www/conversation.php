<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$pdo->query(
    'DELETE FROM private_messages
     WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)'
);

$conversationId = (int)($_GET['id'] ?? 0);
$userId = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT *
     FROM private_conversations
     WHERE id = ?
     AND (
        user_one_id = ?
        OR user_two_id = ?
     )
     LIMIT 1'
);

$stmt->execute([
    $conversationId,
    $userId,
    $userId
]);

$conversation = $stmt->fetch();

if (!$conversation) {
    header('Location: messages.php');
    exit;
}

$friendId = (int)$conversation['user_one_id'] === $userId
    ? (int)$conversation['user_two_id']
    : (int)$conversation['user_one_id'];

$stmt = $pdo->prepare(
    'SELECT id, username, avatar
     FROM users
     WHERE id = ?
     LIMIT 1'
);
$stmt->execute([$friendId]);
$friend = $stmt->fetch();

$stmt = $pdo->prepare(
    'UPDATE private_messages
     SET is_read = 1
     WHERE conversation_id = ?
     AND sender_id != ?'
);
$stmt->execute([
    $conversationId,
    $userId
]);

$stmt = $pdo->prepare(
    'SELECT
        private_messages.*,
        users.username,
        users.avatar
     FROM private_messages
     INNER JOIN users
        ON private_messages.sender_id = users.id
     WHERE private_messages.conversation_id = ?
     ORDER BY private_messages.created_at ASC'
);

$stmt->execute([$conversationId]);
$messages = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">

        <div class="admin-header">
            <div class="mini-user-link">

                <span class="mini-user-avatar">
                    <?php if (!empty($friend['avatar'])): ?>
                        <img
                            src="assets/uploads/<?= htmlspecialchars(basename($friend['avatar'])) ?>"
                            alt="<?= htmlspecialchars($friend['username']) ?>"
                        >
                    <?php else: ?>
                        <?= htmlspecialchars(strtoupper(substr($friend['username'], 0, 1))) ?>
                    <?php endif; ?>
                </span>

                <div>
                    <span class="eyebrow">Conversation privée</span>
                    <h1 class="section-title">
                        <?= htmlspecialchars($friend['username']) ?>
                    </h1>
                </div>

            </div>

            <a href="messages.php" class="btn btn-secondary">
                Retour
            </a>
        </div>

        <div
            class="private-chat"
            id="privateMessagesList"
            data-conversation-id="<?= htmlspecialchars($conversationId) ?>"
        >
            <?php if (empty($messages)): ?>

                <article class="info-box">
                    <h2>Aucun message</h2>
                    <p>Commence la discussion.</p>
                </article>

            <?php else: ?>

                <?php foreach ($messages as $message): ?>
                    <?php $isMine = (int)$message['sender_id'] === $userId; ?>

                    <article class="private-message <?= $isMine ? 'private-message-mine' : 'private-message-other' ?>">

                        <div class="private-message-header">

                            <span class="mini-user-avatar">
                                <?php if (!empty($message['avatar'])): ?>
                                    <img
                                        src="assets/uploads/<?= htmlspecialchars(basename($message['avatar'])) ?>"
                                        alt="<?= htmlspecialchars($message['username']) ?>"
                                    >
                                <?php else: ?>
                                    <?= htmlspecialchars(strtoupper(substr($message['username'], 0, 1))) ?>
                                <?php endif; ?>
                            </span>

                            <strong>
                                <?= htmlspecialchars($message['username']) ?>
                            </strong>

                            <span>
                                <?= htmlspecialchars(date('d/m/Y H:i', strtotime($message['created_at']))) ?>
                            </span>

                        </div>

                        <p>
                            <?= nl2br(htmlspecialchars($message['message'])) ?>
                        </p>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>
        </div>

        <article class="info-box" style="margin-top: 2rem;">
            <h2>Envoyer un message</h2>

            <form
                action="private_message_send.php"
                method="POST"
                id="privateMessageForm"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>"
                >

                <input
                    type="hidden"
                    name="conversation_id"
                    value="<?= htmlspecialchars($conversationId) ?>"
                >

                <div class="form-group">
                    <textarea
                        name="message"
                        id="privateMessageInput"
                        placeholder="Votre message..."
                        maxlength="1000"
                        required
                    ></textarea>
                </div>

                <button class="btn" type="submit">
                    Envoyer
                </button>
            </form>
        </article>

    </div>
</section>

<?php include 'includes/footer.php'; ?>