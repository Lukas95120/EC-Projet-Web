<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

function formatModerationAction(string $action): array
{
    return match ($action) {
        'ban_user' => [
            'label' => 'Bannissement',
            'class' => 'status-rejected'
        ],
        'unban_user' => [
            'label' => 'Débannissement',
            'class' => 'status-added'
        ],
        'delete_review' => [
            'label' => 'Suppression d’avis',
            'class' => 'status-pending'
        ],
        'delete_forum_message' => [
            'label' => 'Suppression message forum',
            'class' => 'status-pending'
        ],
        'delete_game' => [
            'label' => 'Suppression de jeu',
            'class' => 'status-rejected'
        ],
        default => [
            'label' => $action,
            'class' => 'status-pending'
        ],
    };
}

$actionFilter = $_GET['action'] ?? '';
$adminFilter = trim($_GET['admin'] ?? '');
$targetFilter = trim($_GET['target'] ?? '');
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($actionFilter !== '') {
    $where[] = 'admin_moderation_logs.action = ?';
    $params[] = $actionFilter;
}

if ($adminFilter !== '') {
    $where[] = 'admin.username LIKE ?';
    $params[] = '%' . $adminFilter . '%';
}

if ($targetFilter !== '') {
    $where[] = 'target.username LIKE ?';
    $params[] = '%' . $targetFilter . '%';
}

if ($dateFrom !== '') {
    $where[] = 'DATE(admin_moderation_logs.created_at) >= ?';
    $params[] = $dateFrom;
}

if ($dateTo !== '') {
    $where[] = 'DATE(admin_moderation_logs.created_at) <= ?';
    $params[] = $dateTo;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare(
    'SELECT COUNT(*)
     FROM admin_moderation_logs
     LEFT JOIN users AS admin ON admin_moderation_logs.admin_id = admin.id
     LEFT JOIN users AS target ON admin_moderation_logs.target_user_id = target.id
     ' . $whereSql
);
$countStmt->execute($params);

$totalLogs = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalLogs / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$stmt = $pdo->prepare(
    'SELECT
        admin_moderation_logs.*,
        admin.username AS admin_username,
        target.username AS target_username
     FROM admin_moderation_logs
     LEFT JOIN users AS admin ON admin_moderation_logs.admin_id = admin.id
     LEFT JOIN users AS target ON admin_moderation_logs.target_user_id = target.id
     ' . $whereSql . '
     ORDER BY admin_moderation_logs.created_at DESC
     LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset
);

$stmt->execute($params);
$logs = $stmt->fetchAll();

function buildModerationHistoryPageUrl(int $page): string
{
    $params = $_GET;
    $params['page'] = $page;

    return 'moderation_history.php?' . http_build_query($params);
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="admin-header">
            <div>
                <span class="eyebrow">Administration</span>
                <h1 class="section-title">Historique de modération</h1>
            </div>

            <a href="dashboard.php" class="btn btn-secondary">Retour dashboard</a>
        </div>

        <div id="adminAjaxContent">
            <div class="info-box">
                <h2>Filtres</h2>

                <form action="moderation_history.php" method="GET" class="admin-filter-form">
                    <div class="form-group">
                        <label for="action">Type d’action</label>
                        <select id="action" name="action">
                            <option value="">Toutes les actions</option>
                            <option value="ban_user" <?= $actionFilter === 'ban_user' ? 'selected' : '' ?>>
                                Bannissement
                            </option>
                            <option value="unban_user" <?= $actionFilter === 'unban_user' ? 'selected' : '' ?>>
                                Débannissement
                            </option>
                            <option value="delete_review" <?= $actionFilter === 'delete_review' ? 'selected' : '' ?>>
                                Suppression d’avis
                            </option>
                            <option value="delete_forum_message" <?= $actionFilter === 'delete_forum_message' ? 'selected' : '' ?>>
                                Suppression message forum
                            </option>
                            <option value="delete_game" <?= $actionFilter === 'delete_game' ? 'selected' : '' ?>>
                                Suppression de jeu
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="admin">Admin</label>
                        <input
                            type="text"
                            id="admin"
                            name="admin"
                            placeholder="Pseudo admin"
                            value="<?= htmlspecialchars($adminFilter) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="target">Utilisateur concerné</label>
                        <input
                            type="text"
                            id="target"
                            name="target"
                            placeholder="Pseudo utilisateur"
                            value="<?= htmlspecialchars($targetFilter) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="date_from">Date début</label>
                        <input
                            type="date"
                            id="date_from"
                            name="date_from"
                            value="<?= htmlspecialchars($dateFrom) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="date_to">Date fin</label>
                        <input
                            type="date"
                            id="date_to"
                            name="date_to"
                            value="<?= htmlspecialchars($dateTo) ?>"
                        >
                    </div>

                    <button class="btn" type="submit">Filtrer</button>
                    <a href="moderation_history.php" class="btn btn-secondary">Réinitialiser</a>
                </form>
            </div>

            <p class="form-help" style="margin: 1.5rem 0;">
                <?= htmlspecialchars($totalLogs) ?> action(s) trouvée(s)
                — page <?= htmlspecialchars($page) ?> / <?= htmlspecialchars($totalPages) ?>
            </p>

            <div class="table-wrapper" style="margin-top: 2rem;">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Action</th>
                            <th>Admin</th>
                            <th>Utilisateur concerné</th>
                            <th>Raison</th>
                            <th>Détails</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <?php $action = formatModerationAction($log['action']); ?>

                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($log['created_at']))) ?></td>

                                <td>
                                    <span class="status-badge <?= htmlspecialchars($action['class']) ?>">
                                        <?= htmlspecialchars($action['label']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($log['admin_username'] ?? 'Admin supprimé') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($log['target_username'] ?? '—') ?>
                                </td>

                                <td>
                                    <?= !empty($log['reason'])
                                        ? nl2br(htmlspecialchars($log['reason']))
                                        : '—' ?>
                                </td>

                                <td>
                                    <?= !empty($log['details'])
                                        ? nl2br(htmlspecialchars($log['details']))
                                        : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6">Aucune action ne correspond aux filtres.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="catalog-pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= htmlspecialchars(buildModerationHistoryPageUrl($page - 1)) ?>" class="pagination-link">
                            Précédent
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a
                            href="<?= htmlspecialchars(buildModerationHistoryPageUrl($i)) ?>"
                            class="pagination-link <?= $i === $page ? 'pagination-active' : '' ?>"
                        >
                            <?= htmlspecialchars($i) ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= htmlspecialchars(buildModerationHistoryPageUrl($page + 1)) ?>" class="pagination-link">
                            Suivant
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>