<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM games WHERE id = ?');
$stmt->execute([$id]);
$game = $stmt->fetch();

if (!$game) {
    header('Location: games.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT reviews.*, users.username
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

if (isLoggedIn()) {
    $stmt = $pdo->prepare(
        'SELECT 1 FROM favorites WHERE user_id = ? AND game_id = ?'
    );
    $stmt->execute([$_SESSION['user']['id'], $game['id']]);
    $isFavorite = (bool)$stmt->fetch();
}

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title"><?= htmlspecialchars($game['title']) ?></h1>

        <?php if (!empty($game['image_url'])): ?>
            <img
                src="<?= htmlspecialchars($game['image_url']) ?>"
                alt="<?= htmlspecialchars($game['title']) ?>"
                class="game-detail-image"
            >
        <?php endif; ?>

        <div class="details-layout">
            <div class="info-box">
                <h2>Description</h2>
                <p>
                    Fiche détaillée du jeu avec ses statistiques principales,
                    ses ventes, ses notes et les avis utilisateurs.
                </p>

                <h2>Avis utilisateurs</h2>

                <?php if (empty($reviews)): ?>
                    <article class="card">
                        <h3>Aucun avis pour le moment</h3>
                        <p>Sois le premier à laisser une note et un commentaire.</p>
                    </article>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <article class="card">
                            <h3><?= htmlspecialchars($review['username']) ?></h3>

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
                                <form action="delete_review.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                    <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                    <button class="btn btn-danger" type="submit">Supprimer mon avis</button>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                    <form action="add_review.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="game_id" value="<?= $game['id'] ?>">

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
                            <textarea id="comment" name="comment" placeholder="Ton avis sur ce jeu..."></textarea>
                        </div>

                        <button class="btn" type="submit">Ajouter un avis</button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn">Connecte-toi pour laisser un avis</a>
                <?php endif; ?>
            </div>

            <aside class="info-box">
                <h2>Informations</h2>

                <div class="rating-box">
                    <?php if ($averageRating !== null): ?>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= round($averageRating) ? '⭐' : '☆' ?>
                            <?php endfor; ?>
                        </div>

                        <p>
                            <strong><?= htmlspecialchars($averageRating) ?>/5</strong>
                            sur <?= htmlspecialchars($reviewCount) ?> avis
                        </p>
                    <?php else: ?>
                        <p>Aucune note utilisateur pour le moment.</p>
                    <?php endif; ?>
                </div>

                <ul class="info-list">
                    <li><strong>Plateforme :</strong> <?= !empty($game['platform']) ? htmlspecialchars($game['platform']) : 'Non renseignée' ?></li>
                    <li><strong>Genre :</strong> <?= !empty($game['genre']) ? htmlspecialchars($game['genre']) : 'Non renseigné' ?></li>
                    <li><strong>Année :</strong> <?= !empty($game['release_year']) ? htmlspecialchars($game['release_year']) : 'Non renseignée' ?></li>
                    <li><strong>Éditeur :</strong> <?= !empty($game['publisher']) ? htmlspecialchars($game['publisher']) : 'Non renseigné' ?></li>
                    <li><strong>Ventes :</strong> <?= $game['global_sales'] !== null && $game['global_sales'] !== '' ? htmlspecialchars($game['global_sales']) . ' M' : 'Non renseignées' ?></li>
                    <li><strong>Score critique :</strong> <?= $game['critic_score'] !== null && $game['critic_score'] !== '' ? htmlspecialchars($game['critic_score']) : 'Non renseigné' ?></li>
                    <li><strong>Score utilisateur :</strong> <?= $game['user_score'] !== null && $game['user_score'] !== '' ? htmlspecialchars($game['user_score']) : 'Non renseigné' ?></li>
                </ul>

                <?php if (isLoggedIn()): ?>
                    <?php if ($isFavorite): ?>
                        <form action="remove_favorite.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                            <input type="hidden" name="return" value="game.php?id=<?= $game['id'] ?>">
                            <button class="btn btn-danger" type="submit">Retirer des favoris</button>
                        </form>
                    <?php else: ?>
                        <form action="add_favorite.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                            <input type="hidden" name="return" value="game.php?id=<?= $game['id'] ?>">
                            <button class="btn" type="submit">Ajouter aux favoris</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn">Connecte-toi pour ajouter aux favoris</a>
                <?php endif; ?>

                <a href="games.php" class="btn btn-secondary">Retour au catalogue</a>
            </aside>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>