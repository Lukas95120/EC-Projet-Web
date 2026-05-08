<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/content_filter.php';
require_once 'includes/moderation.php';

requireLogin();

$pdo->query('DELETE FROM forum_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 HOUR)');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Requête invalide.';
    } else {
        $message = trim($_POST['message'] ?? '');
        $userId = $_SESSION['user']['id'];
        $contentError = $message !== '' ? validateUserContent($message) : null;

        if ($message === '') {
            $error = 'Le message ne peut pas être vide.';
        } elseif (mb_strlen($message, 'UTF-8') > 1000) {
            $error = 'Le message ne doit pas dépasser 1000 caractères.';
        } elseif ($contentError !== null) {
            logModerationAction($pdo, $userId, 'forum_message', $message, $contentError);

            $error = $contentError;
            $_POST['message'] = '';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO forum_messages (user_id, message)
                 VALUES (?, ?)'
            );
            $stmt->execute([$userId, $message]);

            header('Location: forum.php');
            exit;
        }
    }
}

$stmt = $pdo->query(
    'SELECT forum_messages.*, users.username, users.avatar
     FROM forum_messages
     INNER JOIN users ON forum_messages.user_id = users.id
     ORDER BY forum_messages.created_at DESC'
);
$messages = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Forum communautaire</h1>

        <div class="info-box">
            <h2>Discussion générale</h2>
            <p>Les messages sont automatiquement supprimés après 6 heures.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="forum.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="form-group">
                    <label for="message">Ton message</label>
                    <textarea
                        id="message"
                        name="message"
                        placeholder="Écris un message à la communauté..."
                    ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <button class="btn" type="submit">Envoyer</button>
            </form>
        </div>

        <div class="forum-list" id="forumMessagesList">
            <?php if (empty($messages)): ?>
                <div class="info-box">
                    <h2>Aucun message</h2>
                    <p>Sois le premier à lancer la discussion.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <article class="forum-message">
                        <div class="forum-message-header forum-user-header">
                            <a href="user.php?id=<?= htmlspecialchars($message['user_id']) ?>" class="mini-user-link">
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

                                <strong><?= htmlspecialchars($message['username']) ?></strong>
                            </a>

                            <span><?= htmlspecialchars($message['created_at']) ?></span>
                        </div>

                        <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>