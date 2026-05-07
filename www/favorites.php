<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT games.*, favorites.created_at AS favorite_created_at,
            AVG(reviews.rating) AS avg_rating,
            COUNT(reviews.id) AS review_count
     FROM favorites
     INNER JOIN games ON favorites.game_id = games.id
     LEFT JOIN reviews ON games.id = reviews.game_id
     WHERE favorites.user_id = ?
     GROUP BY games.id, favorites.created_at
     ORDER BY favorites.created_at DESC'
);
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll();

$totalFavorites = count($favorites);

include 'includes/header.php';
?>

<section class="section favorites-section">
    <div class="container">
        <div class="favorites-hero info-box">
            <div>
                <span class="eyebrow">Bibliothèque personnelle</span>
                <h1 class="section-title">Mes favoris</h1>
                <p>Retrouve rapidement les jeux que tu veux garder sous la main.</p>
            </div>

            <div class="favorites-stats">
                <article>
                    <strong id="favoriteCount"><?= htmlspecialchars($totalFavorites) ?></strong>
                    <span>favori(s)</span>
                </article>
            </div>
        </div>

        <?php if (empty($favorites)): ?>
            <div class="info-box">
                <h2>Aucun favori</h2>
                <p>Tu n’as pas encore ajouté de jeux à tes favoris.</p>
                <a href="games.php" class="btn">Explorer les jeux</a>
            </div>
        <?php else: ?>
            <div class="favorites-toolbar info-box">
                <div class="form-group">
                    <label for="favoriteSearch">Rechercher dans mes favoris</label>
                    <input
                        type="text"
                        id="favoriteSearch"
                        placeholder="Ex : Zelda, Action, PS4..."
                    >
                </div>

                <a href="games.php" class="btn btn-secondary">Ajouter d’autres jeux</a>
            </div>

            <div class="games-grid favorites-grid" id="favoritesGrid">
                <?php foreach ($favorites as $game): ?>
                    <article
                        class="game-card favorite-card"
                        data-title="<?= htmlspecialchars(strtolower($game['title'])) ?>"
                        data-genre="<?= htmlspecialchars(strtolower($game['genre'] ?? '')) ?>"
                        data-platform="<?= htmlspecialchars(strtolower($game['platform'] ?? '')) ?>"
                    >
                        <?php if (!empty($game['image_url'])): ?>
                            <img
                                src="<?= htmlspecialchars($game['image_url']) ?>"
                                alt="<?= htmlspecialchars($game['title']) ?>"
                                class="game-image"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="game-image-placeholder">🎮</div>
                        <?php endif; ?>

                        <span class="favorite-badge">❤️ Favori</span>

                        <h2 class="game-title"><?= htmlspecialchars($game['title']) ?></h2>

                        <div class="catalog-card-meta">
                            <span class="badge">
                                <?= !empty($game['genre']) ? htmlspecialchars($game['genre']) : 'Genre inconnu' ?>
                            </span>

                            <span class="badge">
                                <?= !empty($game['platform']) ? htmlspecialchars($game['platform']) : 'Plateforme inconnue' ?>
                            </span>
                        </div>

                        <div class="catalog-score-row">
                            <div>
                                <strong><?= !empty($game['release_year']) ? htmlspecialchars($game['release_year']) : '—' ?></strong>
                                <span>Année</span>
                            </div>

                            <div>
                                <strong><?= $game['critic_score'] !== null && $game['critic_score'] !== '' ? htmlspecialchars($game['critic_score']) : '—' ?></strong>
                                <span>Critique</span>
                            </div>

                            <div>
                                <strong><?= (int)$game['review_count'] > 0 ? htmlspecialchars(round($game['avg_rating'], 1)) . '/5' : '—' ?></strong>
                                <span>Avis</span>
                            </div>
                        </div>

                        <p>
                            Ajouté aux favoris le :
                            <?= htmlspecialchars($game['favorite_created_at']) ?>
                        </p>

                        <div class="catalog-actions">
                            <a href="game.php?id=<?= $game['id'] ?>" class="btn btn-secondary">Voir détails</a>

                            <form
                                action="remove_favorite.php"
                                method="POST"
                                class="favorite-form"
                                data-add-action="add_favorite.php"
                                data-remove-action="remove_favorite.php"
                            >
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                <input type="hidden" name="return" value="favorites.php">

                                <button class="btn btn-danger" type="submit" data-favorite-button>
                                    Retirer
                                </button>

                                <p class="form-help favorite-message" data-favorite-message></p>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="info-box favorites-empty-search" id="favoritesEmptySearch" style="display: none;">
                <h2>Aucun favori trouvé</h2>
                <p>Aucun jeu favori ne correspond à ta recherche.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>