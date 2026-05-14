<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalLogs = (int)$pdo->query('SELECT COUNT(*) FROM moderation_logs')->fetchColumn();
$totalPages = max(1, (int)ceil($totalLogs / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$stmt = $pdo->prepare(
    'SELECT
        moderation_logs.*,
        users.username
     FROM moderation_logs
     INNER JOIN users
        ON moderation_logs.user_id = users.id
     ORDER BY moderation_logs.created_at DESC
     LIMIT :limit OFFSET :offset'
);

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$logs = $stmt->fetchAll();

function buildModerationLogsPageUrl(int $page): string
{
    return 'moderation_logs.php?page=' . $page;
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container">

        <div class="admin-header">
            <div>
                <span class="eyebrow">Administration</span>
                <h1 class="section-title">Logs de modération</h1>
            </div>

            <a href="dashboard.php" class="btn btn-secondary">
                Retour dashboard
            </a>
        </div>

        <div id="adminAjaxContent">
            <p class="form-help" style="margin-bottom: 1.5rem;">
                <?= htmlspecialchars($totalLogs) ?> log(s) trouvé(s)
                — page <?= htmlspecialchars($page) ?> / <?= htmlspecialchars($totalPages) ?>
            </p>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Type</th>
                            <th>Contenu bloqué</th>
                            <th>Raison</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['username']) ?></td>

                                <td><?= htmlspecialchars($log['content_type']) ?></td>

                                <td><?= nl2br(htmlspecialchars($log['blocked_content'])) ?></td>

                                <td><?= htmlspecialchars($log['reason']) ?></td>

                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5">
                                    Aucun log de modération.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="catalog-pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= htmlspecialchars(buildModerationLogsPageUrl($page - 1)) ?>" class="pagination-link">
                            Précédent
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a
                            href="<?= htmlspecialchars(buildModerationLogsPageUrl($i)) ?>"
                            class="pagination-link <?= $i === $page ? 'pagination-active' : '' ?>"
                        >
                            <?= htmlspecialchars($i) ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= htmlspecialchars(buildModerationLogsPageUrl($page + 1)) ?>" class="pagination-link">
                            Suivant
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>