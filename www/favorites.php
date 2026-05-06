<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT games.*
     FROM favorites
     INNER JOIN games ON favorites.game_id = games.id
     WHERE favorites.user_id = ?
     ORDER BY favorites.created_at DESC'
);
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Mes favoris</h1>

        <?php if (empty($favorites)): ?>
            <div class="info-box">
                <h2>Aucun favori</h2>
                <p>Tu n’as pas encore ajouté de jeux à tes favoris.</p>
                <a href="games.php" class="btn">Explorer les jeux</a>
            </div>
        <?php else: ?>
            <div class="games-grid">
                <?php foreach ($favorites as $game): ?>
                    <article class="game-card favorite-card" data-game-id="<?= $game['id'] ?>">
                        <?php if (!empty($game['image_url'])): ?>
                            <img
                                src="<?= htmlspecialchars($game['image_url']) ?>"
                                alt="<?= htmlspecialchars($game['title']) ?>"
                                class="game-image"
                            >
                        <?php else: ?>
                            <div class="game-image-placeholder">🎮</div>
                        <?php endif; ?>

                        <span class="favorite-badge">❤️ Favori</span>

                        <h2 class="game-title"><?= htmlspecialchars($game['title']) ?></h2>

                        <span class="badge">
                            <?= !empty($game['genre']) ? htmlspecialchars($game['genre']) : 'Genre inconnu' ?>
                        </span>

                        <span class="badge">
                            <?= !empty($game['platform']) ? htmlspecialchars($game['platform']) : 'Plateforme inconnue' ?>
                        </span>

                        <p>
                            Année :
                            <?= !empty($game['release_year'])
                                ? htmlspecialchars($game['release_year'])
                                : 'Non renseignée' ?>
                        </p>

                        <p>
                            Ventes mondiales :
                            <?= $game['global_sales'] !== null && $game['global_sales'] !== ''
                                ? htmlspecialchars($game['global_sales']) . ' M'
                                : 'Non renseignées' ?>
                        </p>

                        <a href="game.php?id=<?= $game['id'] ?>" class="btn">Voir détails</a>

                        <form
                            action="remove_favorite.php"
                            method="POST"
                            class="favorite-form favorite-remove-card-form"
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
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>