<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/badges.php';

requireLogin();

$currentUserId = (int)$_SESSION['user']['id'];
$search = trim($_GET['search'] ?? '');

$sql = 'SELECT
            users.*,
            COUNT(DISTINCT reviews.id) AS review_count,
            COUNT(DISTINCT favorites.game_id) AS favorite_count,
            COUNT(DISTINCT tickets.id) AS ticket_count
        FROM users
        LEFT JOIN reviews ON users.id = reviews.user_id
        LEFT JOIN favorites ON users.id = favorites.user_id
        LEFT JOIN tickets ON users.id = tickets.user_id
        WHERE users.id != ?
        AND users.is_banned = 0';

$params = [$currentUserId];

if ($search !== '') {
    $sql .= ' AND users.username LIKE ?';
    $params[] = '%' . $search . '%';
}

$sql .= ' GROUP BY users.id
          ORDER BY users.created_at DESC
          LIMIT 30';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$users = $stmt->fetchAll();

if (empty($users)): ?>
    <article class="info-box">
        <h2>Aucun membre trouvé</h2>
        <p>Aucun utilisateur ne correspond à ta recherche.</p>
    </article>
<?php
    exit;
endif;
?>

<?php foreach ($users as $user): ?>
    <?php
    $userBadges = getUserBadges($user);

    $stmt = $pdo->prepare(
        'SELECT id, status
         FROM friend_requests
         WHERE sender_id = ?
         AND receiver_id = ?
         LIMIT 1'
    );
    $stmt->execute([
        $currentUserId,
        $user['id']
    ]);
    $sentRequest = $stmt->fetch();

    $stmt = $pdo->prepare(
        'SELECT id, status
         FROM friend_requests
         WHERE sender_id = ?
         AND receiver_id = ?
         LIMIT 1'
    );
    $stmt->execute([
        $user['id'],
        $currentUserId
    ]);
    $receivedRequest = $stmt->fetch();

    $stmt = $pdo->prepare(
        'SELECT id
         FROM friendships
         WHERE user_id = ?
         AND friend_id = ?
         LIMIT 1'
    );
    $stmt->execute([
        $currentUserId,
        $user['id']
    ]);
    $friendship = $stmt->fetch();
    ?>

    <article class="info-box admin-user-card">
        <div class="admin-user-top">
            <div class="admin-user-avatar">
                <?php if (!empty($user['avatar'])): ?>
                    <img
                        src="assets/uploads/<?= htmlspecialchars(basename($user['avatar'])) ?>"
                        alt="<?= htmlspecialchars($user['username']) ?>"
                    >
                <?php else: ?>
                    <?= htmlspecialchars(strtoupper(substr($user['username'], 0, 1))) ?>
                <?php endif; ?>
            </div>

            <div>
                <h2>
                    <a href="user.php?id=<?= htmlspecialchars($user['id']) ?>">
                        <?= htmlspecialchars($user['username']) ?>
                    </a>
                </h2>

                <p>
                    Membre depuis le
                    <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) ?>
                </p>

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
        </div>

        <div class="admin-user-actions">
            <a
                href="user.php?id=<?= htmlspecialchars($user['id']) ?>"
                class="btn btn-secondary"
            >
                Voir profil
            </a>

            <?php if ($friendship): ?>
                <a
                    href="messages.php?user_id=<?= htmlspecialchars($user['id']) ?>"
                    class="btn"
                >
                    Message
                </a>
            <?php elseif ($sentRequest && $sentRequest['status'] === 'pending'): ?>
                <span class="status-badge status-pending">
                    Demande envoyée
                </span>
            <?php elseif ($receivedRequest && $receivedRequest['status'] === 'pending'): ?>
                <span class="status-badge status-pending">
                    Demande reçue
                </span>
            <?php else: ?>
                <form action="friend_request_send.php" method="POST">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= generateCsrfToken() ?>"
                    >

                    <input
                        type="hidden"
                        name="receiver_id"
                        value="<?= htmlspecialchars($user['id']) ?>"
                    >

                    <button class="btn" type="submit">
                        Ajouter
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </article>
<?php endforeach; ?>