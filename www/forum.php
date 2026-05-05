<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$pdo->query('DELETE FROM forum_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 HOUR)');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Requête invalide.';
    } else {
        $message = trim($_POST['message'] ?? '');
        $userId = $_SESSION['user']['id'];

        if ($message === '') {
            $error = 'Le message ne peut pas être vide.';
        } elseif (strlen($message) > 1000) {
            $error = 'Le message ne doit pas dépasser 1000 caractères.';
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
    'SELECT forum_messages.*, users.username
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

        <div class="forum-list">
            <?php if (empty($messages)): ?>
                <div class="info-box">
                    <h2>Aucun message</h2>
                    <p>Sois le premier à lancer la discussion.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <article class="forum-message">
                        <div class="forum-message-header">
                            <strong><?= htmlspecialchars($message['username']) ?></strong>
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