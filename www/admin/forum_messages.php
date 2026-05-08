<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$stmt = $pdo->query(
    'SELECT forum_messages.*, users.username, users.avatar
     FROM forum_messages
     INNER JOIN users ON forum_messages.user_id = users.id
     ORDER BY forum_messages.created_at DESC'
);

$messages = $stmt->fetchAll();

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="admin-header">
            <div>
                <span class="eyebrow">Administration</span>
                <h1 class="section-title">Messages du forum</h1>
            </div>

            <a href="dashboard.php" class="btn btn-secondary">Retour dashboard</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td>
                                <a
                                    href="../user.php?id=<?= htmlspecialchars($message['user_id']) ?>"
                                    class="mini-user-link"
                                >
                                    <span class="mini-user-avatar">
                                        <?php if (!empty($message['avatar'])): ?>
                                            <img
                                                src="../assets/uploads/<?= htmlspecialchars(basename($message['avatar'])) ?>"
                                                alt="<?= htmlspecialchars($message['username']) ?>"
                                            >
                                        <?php else: ?>
                                            <?= htmlspecialchars(strtoupper(substr($message['username'], 0, 1))) ?>
                                        <?php endif; ?>
                                    </span>

                                    <strong><?= htmlspecialchars($message['username']) ?></strong>
                                </a>
                            </td>

                            <td><?= nl2br(htmlspecialchars($message['message'])) ?></td>
                            <td><?= htmlspecialchars($message['created_at']) ?></td>

                            <td>
                                <form action="forum_message_delete.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="message_id" value="<?= htmlspecialchars($message['id']) ?>">
                                    <button class="btn btn-danger" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="4">Aucun message trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>