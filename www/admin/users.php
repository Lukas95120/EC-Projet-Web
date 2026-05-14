<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/badges.php';

requireAdmin();

$search = trim($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(users.username LIKE ? OR users.email LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($roleFilter !== '') {
    $where[] = 'users.role = ?';
    $params[] = $roleFilter;
}

if ($statusFilter === 'banned') {
    $where[] = 'users.is_banned = 1';
} elseif ($statusFilter === 'active') {
    $where[] = 'users.is_banned = 0';
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare(
    'SELECT COUNT(*)
     FROM users
     ' . $whereSql
);
$countStmt->execute($params);

$totalUsers = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalUsers / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$stmt = $pdo->prepare(
    'SELECT
        users.*,
        COUNT(DISTINCT reviews.id) AS review_count,
        COUNT(DISTINCT favorites.game_id) AS favorite_count,
        COUNT(DISTINCT tickets.id) AS ticket_count
     FROM users
     LEFT JOIN reviews ON users.id = reviews.user_id
     LEFT JOIN favorites ON users.id = favorites.user_id
     LEFT JOIN tickets ON users.id = tickets.user_id
     ' . $whereSql . '
     GROUP BY users.id
     ORDER BY users.created_at DESC
     LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset
);

$stmt->execute($params);
$users = $stmt->fetchAll();

function buildUsersPageUrl(int $page): string
{
    $params = $_GET;
    $params['page'] = $page;

    return 'users.php?' . http_build_query($params);
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container">

        <div class="admin-header">
            <div>
                <span class="eyebrow">Administration</span>
                <h1 class="section-title">Liste des membres</h1>
            </div>

            <a href="dashboard.php" class="btn btn-secondary">
                Retour dashboard
            </a>
        </div>

        <div id="adminAjaxContent">
            <div class="info-box">
                <h2>Rechercher un membre</h2>

                <form action="users.php" method="GET" class="admin-filter-form">
                    <div class="form-group">
                        <label for="search">Pseudo ou email</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            placeholder="Ex : pseudo, email..."
                            value="<?= htmlspecialchars($search) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="role">Rôle</label>
                        <select id="role" name="role">
                            <option value="">Tous les rôles</option>
                            <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>
                                Utilisateurs
                            </option>
                            <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>
                                Administrateurs
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Statut</label>
                        <select id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>
                                Actifs
                            </option>
                            <option value="banned" <?= $statusFilter === 'banned' ? 'selected' : '' ?>>
                                Bannis
                            </option>
                        </select>
                    </div>

                    <button class="btn" type="submit">Filtrer</button>
                    <a href="users.php" class="btn btn-secondary">Réinitialiser</a>
                </form>
            </div>

            <p class="form-help" style="margin: 1.5rem 0;">
                <?= htmlspecialchars($totalUsers) ?> membre(s) trouvé(s)
                — page <?= htmlspecialchars($page) ?> / <?= htmlspecialchars($totalPages) ?>
            </p>

            <div class="admin-users-grid">

                <?php foreach ($users as $user): ?>
                    <?php $userBadges = getUserBadges($user); ?>

                    <article class="info-box admin-user-card">

                        <div class="admin-user-top">

                            <div class="admin-user-avatar">

                                <?php if (!empty($user['avatar'])): ?>

                                    <?php
                                        $avatarFile = basename($user['avatar']);
                                        $avatarPath = '../assets/uploads/' . $avatarFile;
                                    ?>

                                    <img
                                        src="<?= htmlspecialchars($avatarPath) ?>"
                                        alt="<?= htmlspecialchars($user['username']) ?>"
                                    >

                                <?php else: ?>

                                    <?= htmlspecialchars(strtoupper(substr($user['username'], 0, 1))) ?>

                                <?php endif; ?>

                            </div>

                            <div>
                                <h2>
                                    <a href="../user.php?id=<?= htmlspecialchars($user['id']) ?>">
                                        <?= htmlspecialchars($user['username']) ?>
                                    </a>
                                </h2>

                                <p><?= htmlspecialchars($user['email']) ?></p>

                                <div class="admin-user-role">
                                    <?= $user['role'] === 'admin'
                                        ? '🛡️ Administrateur'
                                        : '👤 Utilisateur' ?>
                                </div>

                                <?php if ((int)$user['is_banned'] === 1): ?>
                                    <span class="status-badge status-rejected">
                                        Compte banni
                                    </span>

                                    <p class="ban-reason-display">
                                        <strong>Raison :</strong>
                                        <?= !empty($user['ban_reason'])
                                            ? htmlspecialchars($user['ban_reason'])
                                            : 'Aucune raison précisée.' ?>
                                    </p>
                                <?php endif; ?>

                                <div class="user-badges">
                                    <?php foreach ($userBadges as $badge): ?>
                                        <span class="user-badge <?= htmlspecialchars($badge['class']) ?>">
                                            <?= htmlspecialchars($badge['icon']) ?>
                                            <?= htmlspecialchars($badge['label']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                        <div class="admin-user-stats">

                            <div>
                                <strong><?= htmlspecialchars($user['review_count']) ?></strong>
                                <span>avis</span>
                            </div>

                            <div>
                                <strong><?= htmlspecialchars($user['favorite_count']) ?></strong>
                                <span>favoris</span>
                            </div>

                            <div>
                                <strong><?= htmlspecialchars($user['ticket_count']) ?></strong>
                                <span>tickets</span>
                            </div>

                            <div>
                                <strong>
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) ?>
                                </strong>

                                <span>inscription</span>
                            </div>

                        </div>

                        <div class="admin-user-actions">

                            <?php if ((int)$user['is_banned'] === 1): ?>

                                <form action="unban_user.php" method="POST" class="admin-user-status-confirm-form">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= generateCsrfToken() ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?= htmlspecialchars($user['id']) ?>"
                                    >

                                    <button class="btn" type="submit">
                                        Débannir
                                    </button>

                                </form>

                            <?php else: ?>

                                <form action="ban_user.php" method="POST" class="admin-user-status-confirm-form">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= generateCsrfToken() ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?= htmlspecialchars($user['id']) ?>"
                                    >

                                    <input
                                        type="text"
                                        name="ban_reason"
                                        placeholder="Raison du bannissement"
                                        class="ban-reason-input"
                                        required
                                    >

                                    <button class="btn btn-danger" type="submit">
                                        Bannir
                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>

                    </article>

                <?php endforeach; ?>

                <?php if (empty($users)): ?>
                    <div class="info-box">
                        <h2>Aucun membre trouvé</h2>
                        <p>Aucun utilisateur ne correspond aux filtres sélectionnés.</p>
                    </div>
                <?php endif; ?>

            </div>

            <?php if ($totalPages > 1): ?>
                <div class="catalog-pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= htmlspecialchars(buildUsersPageUrl($page - 1)) ?>" class="pagination-link">
                            Précédent
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a
                            href="<?= htmlspecialchars(buildUsersPageUrl($i)) ?>"
                            class="pagination-link <?= $i === $page ? 'pagination-active' : '' ?>"
                        >
                            <?= htmlspecialchars($i) ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= htmlspecialchars(buildUsersPageUrl($page + 1)) ?>" class="pagination-link">
                            Suivant
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>