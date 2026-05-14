<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalMessages = (int)$pdo->query('SELECT COUNT(*) FROM forum_messages')->fetchColumn();
$totalPages = max(1, (int)ceil($totalMessages / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$stmt = $pdo->prepare(
    'SELECT forum_messages.*, users.username, users.avatar
     FROM forum_messages
     INNER JOIN users ON forum_messages.user_id = users.id
     ORDER BY forum_messages.created_at DESC
     LIMIT :limit OFFSET :offset'
);

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$messages = $stmt->fetchAll();

function buildForumMessagesPageUrl(int $page): string
{
    return 'forum_messages.php?page=' . $page;
}

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

        <div id="adminAjaxContent">
            <p class="form-help" style="margin-bottom: 1.5rem;">
                <?= htmlspecialchars($totalMessages) ?> message(s) trouvé(s)
                — page <?= htmlspecialchars($page) ?> / <?= htmlspecialchars($totalPages) ?>
            </p>

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
                                    <form action="forum_message_delete.php" method="POST" class="admin-delete-confirm-form">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="message_id" value="<?= htmlspecialchars($message['id']) ?>">

                                        <textarea
                                            name="delete_reason"
                                            placeholder="Raison de suppression..."
                                            required
                                        ></textarea>

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

            <?php if ($totalPages > 1): ?>
                <div class="catalog-pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= htmlspecialchars(buildForumMessagesPageUrl($page - 1)) ?>" class="pagination-link">
                            Précédent
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a
                            href="<?= htmlspecialchars(buildForumMessagesPageUrl($i)) ?>"
                            class="pagination-link <?= $i === $page ? 'pagination-active' : '' ?>"
                        >
                            <?= htmlspecialchars($i) ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= htmlspecialchars(buildForumMessagesPageUrl($page + 1)) ?>" class="pagination-link">
                            Suivant
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>