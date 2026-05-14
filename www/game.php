<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT *
     FROM games
     WHERE id = ?'
);
$stmt->execute([$id]);
$game = $stmt->fetch();

if (!$game) {
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT reviews.*, users.username, users.avatar
     FROM reviews
     INNER JOIN users ON reviews.user_id = users.id
     WHERE reviews.game_id = ?
     ORDER BY reviews.created_at DESC'
);
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT AVG(rating) AS average_rating, COUNT(*) AS review_count
     FROM reviews
     WHERE game_id = ?'
);
$stmt->execute([$id]);
$ratingStats = $stmt->fetch();

$averageRating = $ratingStats['average_rating']
    ? round($ratingStats['average_rating'], 1)
    : null;

$reviewCount = (int)$ratingStats['review_count'];

$isFavorite = false;
$friends = [];

if (isLoggedIn()) {
    $userId = (int)$_SESSION['user']['id'];

    $stmt = $pdo->prepare(
        'SELECT 1
         FROM favorites
         WHERE user_id = ?
         AND game_id = ?'
    );
    $stmt->execute([
        $userId,
        $game['id']
    ]);

    $isFavorite = (bool)$stmt->fetch();

    $stmt = $pdo->prepare(
        'SELECT users.id, users.username
         FROM friendships
         INNER JOIN users ON friendships.friend_id = users.id
         WHERE friendships.user_id = ?
         ORDER BY users.username ASC'
    );
    $stmt->execute([$userId]);

    $friends = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<section class="game-hero">
    <div class="container">
        <div class="game-hero-card">

            <div class="game-hero-image-wrap">
                <?php if (!empty($game['image_url'])): ?>
                    <img
                        src="<?= htmlspecialchars($game['image_url']) ?>"
                        alt="<?= htmlspecialchars($game['title']) ?>"
                        class="game-hero-image"
                        loading="eager"
                        fetchpriority="high"
                    >
                <?php else: ?>
                    <div class="game-hero-placeholder">🎮</div>
                <?php endif; ?>
            </div>

            <div class="game-hero-content">
                <span class="eyebrow">Fiche jeu</span>

                <h1><?= htmlspecialchars($game['title']) ?></h1>

                <div class="game-hero-badges">
                    <span class="badge">
                        <?= !empty($game['genre'])
                            ? htmlspecialchars(explode(',', $game['genre'])[0])
                            : 'Genre inconnu' ?>
                    </span>

                    <span class="badge">
                        <?= !empty($game['platform'])
                            ? htmlspecialchars(explode(',', $game['platform'])[0])
                            : 'Plateforme inconnue' ?>
                    </span>

                    <?php if (!empty($game['release_year'])): ?>
                        <span class="badge">
                            <?= htmlspecialchars($game['release_year']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <p>
                    Consulte les informations principales du jeu, ses scores
                    et les avis laissés par la communauté GameStats.
                </p>

                <div class="game-hero-stats">
                    <div>
                        <strong id="heroAverageRating">
                            <?= $averageRating !== null
                                ? htmlspecialchars($averageRating) . '/5'
                                : '—' ?>
                        </strong>
                        <span>Note communauté</span>
                    </div>

                    <div>
                        <strong id="heroReviewCount">
                            <?= htmlspecialchars($reviewCount) ?>
                        </strong>
                        <span>Avis utilisateur(s)</span>
                    </div>

                    <div>
                        <strong id="heroCriticScore">
                            <?= $game['critic_score'] !== null && $game['critic_score'] !== ''
                                ? htmlspecialchars($game['critic_score'])
                                : '—' ?>
                        </strong>
                        <span>Score critique</span>
                    </div>
                </div>

                <?php if (isLoggedIn()): ?>
                    <form
                        action="<?= $isFavorite ? 'remove_favorite.php' : 'add_favorite.php' ?>"
                        method="POST"
                        class="favorite-form"
                        data-add-action="add_favorite.php"
                        data-remove-action="remove_favorite.php"
                    >
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="game_id" value="<?= htmlspecialchars($game['id']) ?>">
                        <input type="hidden" name="return" value="game.php?id=<?= htmlspecialchars($game['id']) ?>">

                        <button
                            class="btn <?= $isFavorite ? 'btn-danger' : '' ?>"
                            type="submit"
                            data-favorite-button
                        >
                            <?= $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>
                        </button>

                        <p class="form-help favorite-message" data-favorite-message></p>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn">
                        Connecte-toi pour ajouter aux favoris
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<section class="section game-detail-section">
    <div class="container">
        <div class="details-layout">

            <div class="info-box">
                <h2>Description</h2>

                <p>
                    <?= !empty($game['description'])
                        ? nl2br(htmlspecialchars($game['description']))
                        : 'Fiche détaillée du jeu avec ses statistiques principales, ses notes et les avis utilisateurs.' ?>
                </p>

                <h2>Avis utilisateurs</h2>

                <div id="reviewsList">
                    <?php if (empty($reviews)): ?>
                        <article class="card" id="noReviewsMessage">
                            <h3>Aucun avis pour le moment</h3>
                            <p>Sois le premier à laisser une note et un commentaire.</p>
                        </article>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <article class="card review-card" data-review-id="<?= htmlspecialchars($review['id']) ?>">
                                <div class="review-user-header">
                                    <a
                                        href="user.php?id=<?= htmlspecialchars($review['user_id']) ?>"
                                        class="mini-user-link"
                                    >
                                        <span class="mini-user-avatar">
                                            <?php if (!empty($review['avatar'])): ?>
                                                <img
                                                    src="assets/uploads/<?= htmlspecialchars(basename($review['avatar'])) ?>"
                                                    alt="<?= htmlspecialchars($review['username']) ?>"
                                                >
                                            <?php else: ?>
                                                <?= htmlspecialchars(strtoupper(substr($review['username'], 0, 1))) ?>
                                            <?php endif; ?>
                                        </span>

                                        <strong><?= htmlspecialchars($review['username']) ?></strong>
                                    </a>
                                </div>

                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?= $i <= (int)$review['rating'] ? '⭐' : '☆' ?>
                                    <?php endfor; ?>
                                </div>

                                <p>Note : <?= htmlspecialchars($review['rating']) ?>/5</p>

                                <?php if (!empty($review['comment'])): ?>
                                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                <?php endif; ?>

                                <?php if (isLoggedIn() && $_SESSION['user']['id'] == $review['user_id']): ?>
                                    <form action="delete_review.php" method="POST" class="delete-review-form">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id']) ?>">
                                        <input type="hidden" name="game_id" value="<?= htmlspecialchars($game['id']) ?>">

                                        <button class="btn btn-danger" type="submit">
                                            Supprimer mon avis
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (isLoggedIn()): ?>
                    <form action="add_review.php" method="POST" id="reviewForm" class="review-form-premium">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="game_id" value="<?= htmlspecialchars($game['id']) ?>">

                        <h2>Ajouter mon avis</h2>

                        <div class="form-group">
                            <label for="rating">Note</label>

                            <select id="rating" name="rating">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Très bien</option>
                                <option value="3">3 - Moyen</option>
                                <option value="2">2 - Décevant</option>
                                <option value="1">1 - Mauvais</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="comment">Commentaire</label>

                            <textarea
                                id="comment"
                                name="comment"
                                placeholder="Ton avis sur ce jeu..."
                            ></textarea>
                        </div>

                        <button class="btn" type="submit" data-review-submit>
                            Ajouter un avis
                        </button>

                        <p class="form-help" id="reviewMessage"></p>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn">
                        Connecte-toi pour laisser un avis
                    </a>
                <?php endif; ?>
            </div>

            <aside class="info-box game-side-panel">
                <h2>Informations</h2>

                <div class="rating-box" id="ratingBox">
                    <?php if ($averageRating !== null): ?>
                        <div class="rating-stars" id="averageStars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= round($averageRating) ? '⭐' : '☆' ?>
                            <?php endfor; ?>
                        </div>

                        <p id="averageText">
                            <strong><?= htmlspecialchars($averageRating) ?>/5</strong>
                            sur <?= htmlspecialchars($reviewCount) ?> avis
                        </p>
                    <?php else: ?>
                        <div class="rating-stars" id="averageStars"></div>
                        <p id="averageText">
                            Aucune note utilisateur pour le moment.
                        </p>
                    <?php endif; ?>
                </div>

                <ul class="info-list">
                    <li>
                        <strong>Plateforme :</strong>
                        <?= !empty($game['platform'])
                            ? htmlspecialchars($game['platform'])
                            : 'Non renseignée' ?>
                    </li>

                    <li>
                        <strong>Genre :</strong>
                        <?= !empty($game['genre'])
                            ? htmlspecialchars($game['genre'])
                            : 'Non renseigné' ?>
                    </li>

                    <li>
                        <strong>Année :</strong>
                        <?= !empty($game['release_year'])
                            ? htmlspecialchars($game['release_year'])
                            : 'Non renseignée' ?>
                    </li>

                    <li>
                        <strong>Éditeur :</strong>
                        <?= !empty($game['publisher'])
                            ? htmlspecialchars($game['publisher'])
                            : 'Non renseigné' ?>
                    </li>

                    <li>
                        <strong>Score critique :</strong>
                        <?= $game['critic_score'] !== null && $game['critic_score'] !== ''
                            ? htmlspecialchars($game['critic_score'])
                            : 'Non renseigné' ?>
                    </li>

                    <li>
                        <strong>Score utilisateur :</strong>
                        <?= $game['user_score'] !== null && $game['user_score'] !== ''
                            ? htmlspecialchars($game['user_score'])
                            : 'Non renseigné' ?>
                    </li>
                </ul>

                <a href="games.php" class="btn btn-secondary">
                    Retour au catalogue
                </a>

                <?php if (isLoggedIn()): ?>
                    <div class="info-box" style="margin-top: 1rem;">
                        <h2>Partager ce jeu</h2>

                        <?php if (empty($friends)): ?>
                            <p>Ajoute des amis pour pouvoir leur recommander ce jeu.</p>

                            <a href="friends.php" class="btn btn-secondary">
                                Voir mes amis
                            </a>
                        <?php else: ?>
                            <form action="share_game.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="game_id" value="<?= htmlspecialchars($game['id']) ?>">

                                <div class="form-group">
                                    <label for="friend_id">Choisir un ami</label>

                                    <select id="friend_id" name="friend_id" required>
                                        <?php foreach ($friends as $friend): ?>
                                            <option value="<?= htmlspecialchars($friend['id']) ?>">
                                                <?= htmlspecialchars($friend['username']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="share_message">Message optionnel</label>

                                    <textarea
                                        id="share_message"
                                        name="message"
                                        placeholder="Ex : Je pense que ce jeu pourrait te plaire..."
                                        maxlength="500"
                                    ></textarea>
                                </div>

                                <button class="btn" type="submit">
                                    Recommander ce jeu
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </aside>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>