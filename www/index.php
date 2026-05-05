<?php
require_once 'includes/db.php';
include 'includes/header.php';

/* TOP ventes */
$stmt = $pdo->query(
    'SELECT * FROM games ORDER BY global_sales DESC LIMIT 3'
);
$topSales = $stmt->fetchAll();

/* TOP 3 mieux notés par les utilisateurs */
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

/* TOP scores critiques */
$stmt = $pdo->query(
    'SELECT * FROM games ORDER BY critic_score DESC LIMIT 3'
);
$topScores = $stmt->fetchAll();

/* COUNT */
$totalGames = $pdo->query('SELECT COUNT(*) FROM games')->fetchColumn();
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
                    <strong>Top</strong>
                    <span>ventes</span>
                </div>
                <div>
                    <strong>Users</strong>
                    <span>actifs</span>
                </div>
            </div>
        </div>

        <div class="home-visual">
            <div class="glow-circle"></div>

            <?php if (!empty($topSales)): ?>
                <div class="game-cover cover-main">
                    <span class="cover-label">TOP SALES</span>
                    <h2><?= htmlspecialchars($topSales[0]['title']) ?></h2>
                    <p><?= htmlspecialchars($topSales[0]['platform']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($topSales[1])): ?>
                <div class="game-cover cover-left">
                    <span><?= htmlspecialchars($topSales[1]['genre']) ?></span>
                    <h3><?= htmlspecialchars($topSales[1]['title']) ?></h3>
                </div>
            <?php endif; ?>

            <?php if (isset($topScores[0])): ?>
                <div class="game-cover cover-right">
                    <span>⭐ <?= htmlspecialchars($topScores[0]['critic_score']) ?></span>
                    <h3><?= htmlspecialchars($topScores[0]['title']) ?></h3>
                </div>
            <?php endif; ?>

            <?php if (isset($topScores[0])): ?>
                <div class="floating-card floating-one">
                    ⭐ <?= htmlspecialchars($topScores[0]['critic_score']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($topSales)): ?>
                <div class="floating-card floating-two">
                    🎮 <?= htmlspecialchars($topSales[0]['global_sales']) ?>M
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
                        <span class="ranking-badge">#<?= $index + 1 ?></span>

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

                        <span class="badge"><?= htmlspecialchars($game['genre']) ?></span>
                        <span class="badge"><?= htmlspecialchars($game['platform']) ?></span>

                        <p>Ventes : <?= htmlspecialchars($game['global_sales']) ?> M</p>

                        <a href="game.php?id=<?= $game['id'] ?>" class="btn">Voir détails</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>