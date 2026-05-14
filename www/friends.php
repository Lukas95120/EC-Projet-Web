<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$userId = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT
        friend_requests.*,
        users.username,
        users.avatar
     FROM friend_requests
     INNER JOIN users ON friend_requests.sender_id = users.id
     WHERE friend_requests.receiver_id = ?
     AND friend_requests.status = "pending"
     ORDER BY friend_requests.created_at DESC'
);
$stmt->execute([$userId]);
$receivedRequests = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT
        friend_requests.*,
        users.username,
        users.avatar
     FROM friend_requests
     INNER JOIN users ON friend_requests.receiver_id = users.id
     WHERE friend_requests.sender_id = ?
     AND friend_requests.status = "pending"
     ORDER BY friend_requests.created_at DESC'
);
$stmt->execute([$userId]);
$sentRequests = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT
        users.id,
        users.username,
        users.avatar,
        users.bio,
        friendships.created_at AS friendship_created_at
     FROM friendships
     INNER JOIN users ON friendships.friend_id = users.id
     WHERE friendships.user_id = ?
     ORDER BY friendships.created_at DESC'
);
$stmt->execute([$userId]);
$friends = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="admin-header">
            <div>
                <span class="eyebrow">Communauté</span>
                <h1 class="section-title">Mes amis</h1>
            </div>

            <a href="profile.php" class="btn btn-secondary">Retour profil</a>
        </div>

        <div class="dashboard-grid">
            <article class="info-box">
                <h2>Demandes reçues</h2>

                <?php if (empty($receivedRequests)): ?>
                    <p>Aucune demande d’ami reçue.</p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($receivedRequests as $request): ?>
                            <li>
                                <strong>
                                    <a href="user.php?id=<?= htmlspecialchars($request['sender_id']) ?>">
                                        <?= htmlspecialchars($request['username']) ?>
                                    </a>
                                </strong>

                                <span>
                                    Reçue le <?= htmlspecialchars(date('d/m/Y H:i', strtotime($request['created_at']))) ?>
                                </span>

                                <div class="notification-actions" style="margin-top: 0.8rem;">
                                    <form action="friend_request_accept.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">

                                        <button class="btn" type="submit">
                                            Accepter
                                        </button>
                                    </form>

                                    <form action="friend_request_reject.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">

                                        <button class="btn btn-secondary" type="submit">
                                            Refuser
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Demandes envoyées</h2>

                <?php if (empty($sentRequests)): ?>
                    <p>Aucune demande envoyée en attente.</p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($sentRequests as $request): ?>
                            <li>
                                <strong>
                                    <a href="user.php?id=<?= htmlspecialchars($request['receiver_id']) ?>">
                                        <?= htmlspecialchars($request['username']) ?>
                                    </a>
                                </strong>

                                <span>
                                    Envoyée le <?= htmlspecialchars(date('d/m/Y H:i', strtotime($request['created_at']))) ?>
                                </span>

                                <span class="status-badge status-pending">
                                    En attente
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Liste d’amis</h2>

                <?php if (empty($friends)): ?>
                    <p>Tu n’as pas encore d’amis.</p>
                    <a href="community.php" class="btn btn-secondary">Découvrir la communauté</a>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($friends as $friend): ?>
                            <li>
                                <strong>
                                    <a href="user.php?id=<?= htmlspecialchars($friend['id']) ?>">
                                        <?= htmlspecialchars($friend['username']) ?>
                                    </a>
                                </strong>

                                <span>
                                    Ami depuis le <?= htmlspecialchars(date('d/m/Y', strtotime($friend['friendship_created_at']))) ?>
                                </span>

                                <div class="notification-actions" style="margin-top: 0.8rem;">
                                    <a href="user.php?id=<?= htmlspecialchars($friend['id']) ?>" class="btn btn-secondary">
                                        Voir profil
                                    </a>

                                    <a href="messages.php?user_id=<?= htmlspecialchars($friend['id']) ?>" class="btn">
                                        Message
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>