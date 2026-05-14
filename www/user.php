<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/badges.php';

$userId = (int)($_GET['id'] ?? 0);

if ($userId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit;
}

$currentUserId = isLoggedIn() ? (int)$_SESSION['user']['id'] : 0;
$isOwnProfile = $currentUserId === (int)$user['id'];

$friendshipStatus = null;
$friendRequest = null;

if (isLoggedIn() && !$isOwnProfile) {
    $stmt = $pdo->prepare(
        'SELECT id
         FROM friendships
         WHERE user_id = ?
         AND friend_id = ?
         LIMIT 1'
    );
    $stmt->execute([$currentUserId, $userId]);

    if ($stmt->fetch()) {
        $friendshipStatus = 'friends';
    } else {
        $stmt = $pdo->prepare(
            'SELECT *
             FROM friend_requests
             WHERE (
                sender_id = ?
                AND receiver_id = ?
             )
             OR (
                sender_id = ?
                AND receiver_id = ?
             )
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute([
            $currentUserId,
            $userId,
            $userId,
            $currentUserId
        ]);

        $friendRequest = $stmt->fetch();

        if ($friendRequest) {
            if ($friendRequest['status'] === 'pending') {
                $friendshipStatus = (int)$friendRequest['sender_id'] === $currentUserId
                    ? 'request_sent'
                    : 'request_received';
            } elseif ($friendRequest['status'] === 'rejected') {
                $friendshipStatus = 'rejected';
            }
        }
    }
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM favorites WHERE user_id = ?');
$stmt->execute([$userId]);
$favoriteCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$stmt->execute([$userId]);
$reviewCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM tickets WHERE user_id = ?');
$stmt->execute([$userId]);
$ticketCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT AVG(rating) FROM reviews WHERE user_id = ?');
$stmt->execute([$userId]);
$averageGivenRating = $stmt->fetchColumn();
$averageGivenRating = $averageGivenRating ? round($averageGivenRating, 1) : null;

$userForBadges = $user;
$userForBadges['review_count'] = $reviewCount;
$userForBadges['favorite_count'] = $favoriteCount;
$userForBadges['ticket_count'] = $ticketCount;

$userBadges = getUserBadges($userForBadges);

$stmt = $pdo->prepare(
    'SELECT games.*
     FROM favorites
     INNER JOIN games ON favorites.game_id = games.id
     WHERE favorites.user_id = ?
     ORDER BY favorites.created_at DESC
     LIMIT 6'
);
$stmt->execute([$userId]);
$latestFavorites = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT reviews.*, games.title, games.id AS game_id
     FROM reviews
     INNER JOIN games ON reviews.game_id = games.id
     WHERE reviews.user_id = ?
     ORDER BY reviews.created_at DESC
     LIMIT 6'
);
$stmt->execute([$userId]);
$latestReviews = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="profile-grid">
            <aside class="info-box avatar-box">
                <?php if (!empty($user['avatar'])): ?>
                    <img
                        src="assets/uploads/<?= htmlspecialchars(basename($user['avatar'])) ?>"
                        alt="<?= htmlspecialchars($user['username']) ?>"
                        class="profile-avatar"
                    >
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?= htmlspecialchars(strtoupper(substr($user['username'], 0, 1))) ?>
                    </div>
                <?php endif; ?>

                <h1><?= htmlspecialchars($user['username']) ?></h1>
                <p>Membre GameStats depuis le <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) ?></p>

                <div class="user-badges">
                    <?php foreach ($userBadges as $badge): ?>
                        <span class="user-badge <?= htmlspecialchars($badge['class']) ?>">
                            <?= htmlspecialchars($badge['icon']) ?>
                            <?= htmlspecialchars($badge['label']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="profile-bio-preview">
                    <h3>Bio</h3>
                    <p>
                        <?= !empty($user['bio'])
                            ? nl2br(htmlspecialchars($user['bio']))
                            : 'Aucune biographie renseignée.' ?>
                    </p>
                </div>
            </aside>

            <div class="info-box">
                <span class="eyebrow">Profil public</span>
                <h2>Activité de <?= htmlspecialchars($user['username']) ?></h2>

                <p>
                    Consulte les badges, les favoris récents et les derniers avis publiés
                    par ce membre de la communauté GameStats.
                </p>

                <?php if ($isOwnProfile): ?>
                    <a href="profile.php" class="btn">Modifier mon profil</a>
                <?php elseif (isLoggedIn()): ?>

                    <?php if ($friendshipStatus === 'friends'): ?>
                        <span class="status-badge status-added">Déjà amis</span>
                        <a href="messages.php?user_id=<?= htmlspecialchars($user['id']) ?>" class="btn">
                            Envoyer un message
                        </a>

                    <?php elseif ($friendshipStatus === 'request_sent'): ?>
                        <span class="status-badge status-pending">Demande envoyée</span>

                    <?php elseif ($friendshipStatus === 'request_received' && $friendRequest): ?>
                        <form action="friend_request_accept.php" method="POST" style="display:inline-block;">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($friendRequest['id']) ?>">
                            <button class="btn" type="submit">Accepter</button>
                        </form>

                        <form action="friend_request_reject.php" method="POST" style="display:inline-block;">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($friendRequest['id']) ?>">
                            <button class="btn btn-secondary" type="submit">Refuser</button>
                        </form>

                    <?php else: ?>
                        <form action="friend_request_send.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($user['id']) ?>">
                            <button class="btn" type="submit">Ajouter en ami</button>
                        </form>
                    <?php endif; ?>

                <?php else: ?>
                    <a href="login.php" class="btn">Connecte-toi pour ajouter ce membre</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-stats" style="margin-top: 2rem;">
            <article class="stat-card">
                <span>❤️</span>
                <h3><?= htmlspecialchars($favoriteCount) ?></h3>
                <p>Favoris</p>
            </article>

            <article class="stat-card">
                <span>⭐</span>
                <h3><?= htmlspecialchars($reviewCount) ?></h3>
                <p>Avis publiés</p>
            </article>

            <article class="stat-card">
                <span>🎫</span>
                <h3><?= htmlspecialchars($ticketCount) ?></h3>
                <p>Contributions</p>
            </article>

            <article class="stat-card">
                <span>🎯</span>
                <h3><?= $averageGivenRating !== null ? htmlspecialchars($averageGivenRating) : '—' ?></h3>
                <p>Note moyenne donnée</p>
            </article>
        </div>

        <div class="dashboard-grid">
            <article class="info-box">
                <h2>Favoris récents</h2>

                <?php if (empty($latestFavorites)): ?>
                    <p>Ce membre n’a pas encore de favoris publics.</p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($latestFavorites as $favorite): ?>
                            <li>
                                <strong>
                                    <a href="game.php?id=<?= htmlspecialchars($favorite['id']) ?>">
                                        <?= htmlspecialchars($favorite['title']) ?>
                                    </a>
                                </strong>

                                <span>
                                    <?= !empty($favorite['genre']) ? htmlspecialchars($favorite['genre']) : 'Genre inconnu' ?>
                                    ·
                                    <?= !empty($favorite['platform']) ? htmlspecialchars($favorite['platform']) : 'Plateforme inconnue' ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Derniers avis</h2>

                <?php if (empty($latestReviews)): ?>
                    <p>Ce membre n’a pas encore publié d’avis.</p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($latestReviews as $review): ?>
                            <li>
                                <strong>
                                    <a href="game.php?id=<?= htmlspecialchars($review['game_id']) ?>">
                                        <?= htmlspecialchars($review['title']) ?>
                                    </a>
                                </strong>

                                <span><?= htmlspecialchars($review['rating']) ?>/5</span>

                                <?php if (!empty($review['comment'])): ?>
                                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Communauté</h2>
                <p>
                    Les profils publics permettent de découvrir les membres actifs,
                    leurs badges et leurs jeux préférés.
                </p>

                <a href="games.php" class="btn btn-secondary">Explorer le catalogue</a>
            </article>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>