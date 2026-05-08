<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/badges.php';

requireAdmin();

$stmt = $pdo->query(
    'SELECT
        users.*,
        COUNT(DISTINCT reviews.id) AS review_count,
        COUNT(DISTINCT favorites.game_id) AS favorite_count,
        COUNT(DISTINCT tickets.id) AS ticket_count
     FROM users
     LEFT JOIN reviews ON users.id = reviews.user_id
     LEFT JOIN favorites ON users.id = favorites.user_id
     LEFT JOIN tickets ON users.id = tickets.user_id
     GROUP BY users.id
     ORDER BY users.created_at DESC'
);

$users = $stmt->fetchAll();

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

                            <form action="unban_user.php" method="POST">

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

                            <form action="ban_user.php" method="POST">

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

                                <button class="btn btn-danger" type="submit">
                                    Bannir
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>