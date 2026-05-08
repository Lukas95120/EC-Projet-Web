<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$stmt = $pdo->query(
    'SELECT
        moderation_logs.*,
        users.username
     FROM moderation_logs
     INNER JOIN users
        ON moderation_logs.user_id = users.id
     ORDER BY moderation_logs.created_at DESC'
);

$logs = $stmt->fetchAll();

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

                            <td>
                                <?= htmlspecialchars($log['content_type']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['blocked_content']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['reason']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['created_at']) ?>
                            </td>
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

    </div>
</section>

<?php include '../includes/footer.php'; ?>