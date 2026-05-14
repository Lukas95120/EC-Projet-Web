<?php
require_once 'includes/db.php';
include 'includes/header.php';

$stmt = $pdo->query(
    'SELECT *
     FROM games
     WHERE critic_score IS NOT NULL
     ORDER BY critic_score DESC, id DESC
     LIMIT 3'
);
$featuredGames = $stmt->fetchAll();

$stmt = $pdo->query(
    'SELECT games.*,
            AVG(reviews.rating) AS avg_rating,
            COUNT(reviews.id) AS review_count
     FROM games
     INNER JOIN reviews ON games.id = reviews.game_id
     GROUP BY games.id
     HAVING review_count > 0
     ORDER BY avg_rating DESC, review_count DESC
     LIMIT 3'
);
$topRatedGames = $stmt->fetchAll();

$stmt = $pdo->query(
    'SELECT *
     FROM games
     WHERE critic_score IS NOT NULL
     ORDER BY critic_score DESC
     LIMIT 3'
);
$topScores = $stmt->fetchAll();

$stmt = $pdo->query(
    'SELECT *
     FROM games
     ORDER BY id DESC
     LIMIT 3'
);
$latestGames = $stmt->fetchAll();

$stmt = $pdo->query(
    'SELECT reviews.*, users.username, games.title, games.id AS game_id
     FROM reviews
     INNER JOIN users ON reviews.user_id = users.id
     INNER JOIN games ON reviews.game_id = games.id
     ORDER BY reviews.created_at DESC
     LIMIT 3'
);
$latestReviews = $stmt->fetchAll();

$totalGames = (int)$pdo->query('SELECT COUNT(*) FROM games')->fetchColumn();
$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalReviews = (int)$pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
?>

<section class="home-screen">
    <div class="container home-grid">

        <div class="home-content">
            <span class="eyebrow">Projet Web 2025-2026</span>

            <h1>Explore les jeux vidéo comme jamais.</h1>

            <p>
                Recherche, compare, note et sauvegarde tes jeux favoris grâce à une interface moderne basée sur des données réelles.
            </p>

            <div class="home-actions">
                <a href="games.php" class="btn">Explorer les jeux</a>

                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-secondary">Créer un compte</a>
                <?php else: ?>
                    <a href="profile.php" class="btn btn-secondary">Voir mon profil</a>
                <?php endif; ?>
            </div>

            <div class="home-mini-stats">
                <div>
                    <strong><?= htmlspecialchars($totalGames) ?></strong>
                    <span>jeux</span>
                </div>

                <div>
                    <strong><?= htmlspecialchars($totalUsers) ?></strong>
                    <span>utilisateurs</span>
                </div>

                <div>
                    <strong><?= htmlspecialchars($totalReviews) ?></strong>
                    <span>avis</span>
                </div>
            </div>
        </div>

        <div class="home-visual">
            <div class="glow-circle"></div>

            <?php if (!empty($featuredGames)): ?>
                <a href="game.php?id=<?= htmlspecialchars($featuredGames[0]['id']) ?>" class="game-cover cover-main home-cover-image">
                    <?php if (!empty($featuredGames[0]['image_url'])): ?>
                        <img src="<?= htmlspecialchars($featuredGames[0]['image_url']) ?>" alt="<?= htmlspecialchars($featuredGames[0]['title']) ?>">
                    <?php endif; ?>

                    <span class="cover-label">À DÉCOUVRIR</span>
                    <h2><?= htmlspecialchars($featuredGames[0]['title']) ?></h2>
                    <p><?= !empty($featuredGames[0]['platform']) ? htmlspecialchars(explode(',', $featuredGames[0]['platform'])[0]) : 'Plateforme inconnue' ?></p>
                </a>
            <?php endif; ?>

            <?php if (isset($featuredGames[1])): ?>
                <a href="game.php?id=<?= htmlspecialchars($featuredGames[1]['id']) ?>" class="game-cover cover-left home-cover-image">
                    <?php if (!empty($featuredGames[1]['image_url'])): ?>
                        <img src="<?= htmlspecialchars($featuredGames[1]['image_url']) ?>" alt="<?= htmlspecialchars($featuredGames[1]['title']) ?>">
                    <?php endif; ?>

                    <span><?= !empty($featuredGames[1]['genre']) ? htmlspecialchars(explode(',', $featuredGames[1]['genre'])[0]) : 'Genre inconnu' ?></span>
                    <h3><?= htmlspecialchars($featuredGames[1]['title']) ?></h3>
                </a>
            <?php endif; ?>

            <?php if (isset($topScores[0])): ?>
                <a href="game.php?id=<?= htmlspecialchars($topScores[0]['id']) ?>" class="game-cover cover-right home-cover-image">
                    <?php if (!empty($topScores[0]['image_url'])): ?>
                        <img src="<?= htmlspecialchars($topScores[0]['image_url']) ?>" alt="<?= htmlspecialchars($topScores[0]['title']) ?>">
                    <?php endif; ?>

                    <span>⭐ <?= htmlspecialchars($topScores[0]['critic_score']) ?></span>
                    <h3><?= htmlspecialchars($topScores[0]['title']) ?></h3>
                </a>

                <div class="floating-card floating-one">
                    ⭐ <?= htmlspecialchars($topScores[0]['critic_score']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($featuredGames)): ?>
                <div class="floating-card floating-two">
                    🎮 Jeux
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section home-top-section">
    <div class="container">
        <h2 class="section-title">Top 3 des jeux les mieux notés</h2>

        <?php if (empty($topRatedGames)): ?>
            <div class="info-box">
                <h2>Aucune note pour le moment</h2>
                <p>Les jeux les mieux notés apparaîtront ici dès que des utilisateurs laisseront des avis.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($topRatedGames as $index => $game): ?>
                    <article class="game-card top-game-card">
                        <span class="ranking-badge">#<?= htmlspecialchars($index + 1) ?></span>

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

                        <h2 class="game-title"><?= htmlspecialchars($game['title']) ?></h2>

                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= round($game['avg_rating']) ? '⭐' : '☆' ?>
                            <?php endfor; ?>
                        </div>

                        <p>
                            <strong><?= htmlspecialchars(round($game['avg_rating'], 1)) ?>/5</strong>
                            sur <?= htmlspecialchars($game['review_count']) ?> avis
                        </p>

                        <span class="badge"><?= !empty($game['genre']) ? htmlspecialchars(explode(',', $game['genre'])[0]) : 'Genre inconnu' ?></span>
                        <span class="badge"><?= !empty($game['platform']) ? htmlspecialchars(explode(',', $game['platform'])[0]) : 'Plateforme inconnue' ?></span>

                        <a href="game.php?id=<?= htmlspecialchars($game['id']) ?>" class="btn">Voir détails</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Derniers jeux ajoutés</h2>

        <?php if (empty($latestGames)): ?>
            <div class="info-box">
                <h2>Aucun jeu récent</h2>
                <p>Les derniers jeux ajoutés apparaîtront ici.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($latestGames as $game): ?>
                    <article class="game-card">
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

                        <h2 class="game-title"><?= htmlspecialchars($game['title']) ?></h2>

                        <span class="badge"><?= !empty($game['genre']) ? htmlspecialchars(explode(',', $game['genre'])[0]) : 'Genre inconnu' ?></span>
                        <span class="badge"><?= !empty($game['platform']) ? htmlspecialchars(explode(',', $game['platform'])[0]) : 'Plateforme inconnue' ?></span>

                        <p>
                            Année :
                            <?= !empty($game['release_year'])
                                ? htmlspecialchars($game['release_year'])
                                : 'Non renseignée' ?>
                        </p>

                        <a href="game.php?id=<?= htmlspecialchars($game['id']) ?>" class="btn">Voir détails</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Derniers avis de la communauté</h2>

        <?php if (empty($latestReviews)): ?>
            <div class="info-box">
                <h2>Aucun avis récent</h2>
                <p>Les derniers avis publiés par les utilisateurs apparaîtront ici.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($latestReviews as $review): ?>
                    <article class="card">
                        <h3><?= htmlspecialchars($review['title']) ?></h3>

                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= (int)$review['rating'] ? '⭐' : '☆' ?>
                            <?php endfor; ?>
                        </div>

                        <p>
                            <strong><?= htmlspecialchars($review['username']) ?></strong>
                            a donné <?= htmlspecialchars($review['rating']) ?>/5
                        </p>

                        <?php if (!empty($review['comment'])): ?>
                            <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        <?php endif; ?>

                        <a href="game.php?id=<?= htmlspecialchars($review['game_id']) ?>" class="btn btn-secondary">Voir le jeu</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>