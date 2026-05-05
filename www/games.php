<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$search = trim($_GET['search'] ?? '');
$genre = trim($_GET['genre'] ?? '');
$platform = trim($_GET['platform'] ?? '');
$year = trim($_GET['year'] ?? '');
$sort = $_GET['sort'] ?? 'title_asc';

$userId = isLoggedIn() ? $_SESSION['user']['id'] : null;

$sql = 'SELECT games.*, 
               AVG(reviews.rating) AS avg_rating,
               COUNT(reviews.id) AS review_count';

if ($userId) {
    $sql .= ', favorites.user_id AS is_favorite';
}

$sql .= ' FROM games
          LEFT JOIN reviews ON games.id = reviews.game_id';

if ($userId) {
    $sql .= ' LEFT JOIN favorites 
              ON games.id = favorites.game_id 
              AND favorites.user_id = ?';
}

$sql .= ' WHERE 1=1';

$params = [];

if ($userId) {
    $params[] = $userId;
}

if ($search !== '') {
    $sql .= ' AND games.title LIKE ?';
    $params[] = '%' . $search . '%';
}

if ($genre !== '') {
    $sql .= ' AND games.genre = ?';
    $params[] = $genre;
}

if ($platform !== '') {
    $sql .= ' AND games.platform = ?';
    $params[] = $platform;
}

if ($year !== '') {
    $sql .= ' AND games.release_year = ?';
    $params[] = $year;
}

$sql .= ' GROUP BY games.id';

$allowedSorts = [
    'title_asc' => 'games.title ASC',
    'sales_desc' => 'games.global_sales DESC',
    'score_desc' => 'games.critic_score DESC',
    'rating_desc' => 'avg_rating DESC',
    'year_desc' => 'games.release_year DESC',
    'year_asc' => 'games.release_year ASC'
];

$orderBy = $allowedSorts[$sort] ?? $allowedSorts['title_asc'];
$sql .= ' ORDER BY ' . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$games = $stmt->fetchAll();

$genres = $pdo->query('SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL AND genre != "" ORDER BY genre')->fetchAll();
$platforms = $pdo->query('SELECT DISTINCT platform FROM games WHERE platform IS NOT NULL AND platform != "" ORDER BY platform')->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Catalogue des jeux</h1>

        <div class="games-layout">
            <aside class="filters">
                <h2>Filtres</h2>

                <form action="games.php" method="GET">
                    <div class="form-group">
                        <label for="search">Nom du jeu</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            placeholder="Ex : Mario"
                            value="<?= htmlspecialchars($search) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre">
                            <option value="">Tous</option>
                            <?php foreach ($genres as $g): ?>
                                <option value="<?= htmlspecialchars($g['genre']) ?>" <?= $genre === $g['genre'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g['genre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="platform">Plateforme</label>
                        <select id="platform" name="platform">
                            <option value="">Toutes</option>
                            <?php foreach ($platforms as $p): ?>
                                <option value="<?= htmlspecialchars($p['platform']) ?>" <?= $platform === $p['platform'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['platform']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year">Année</label>
                        <input
                            type="number"
                            id="year"
                            name="year"
                            placeholder="Ex : 2010"
                            value="<?= htmlspecialchars($year) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="sort">Trier par</label>
                        <select id="sort" name="sort">
                            <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Titre A-Z</option>
                            <option value="sales_desc" <?= $sort === 'sales_desc' ? 'selected' : '' ?>>Meilleures ventes</option>
                            <option value="score_desc" <?= $sort === 'score_desc' ? 'selected' : '' ?>>Meilleur score critique</option>
                            <option value="rating_desc" <?= $sort === 'rating_desc' ? 'selected' : '' ?>>Meilleures notes utilisateurs</option>
                            <option value="year_desc" <?= $sort === 'year_desc' ? 'selected' : '' ?>>Année récente</option>
                            <option value="year_asc" <?= $sort === 'year_asc' ? 'selected' : '' ?>>Année ancienne</option>
                        </select>
                    </div>

                    <button class="btn" type="submit">Rechercher</button>
                    <a href="games.php" class="btn btn-secondary">Réinitialiser</a>
                </form>
            </aside>

            <div>
                <div class="info-box" style="margin-bottom: 1.5rem;">
                    <h2><?= count($games) ?> résultat(s)</h2>
                    <p>Utilise les filtres pour affiner ta recherche dans le catalogue.</p>
                </div>

                <div class="games-grid">
                    <?php foreach ($games as $game): ?>
                        <article class="game-card">
                            <?php if (!empty($game['image_url'])): ?>
                                <img
                                    src="<?= htmlspecialchars($game['image_url']) ?>"
                                    alt="<?= htmlspecialchars($game['title']) ?>"
                                    class="game-image"
                                >
                            <?php else: ?>
                                <div class="game-image-placeholder">🎮</div>
                            <?php endif; ?>

                            <?php if ($userId && !empty($game['is_favorite'])): ?>
                                <span class="favorite-badge">❤️ Favori</span>
                            <?php endif; ?>

                            <h2 class="game-title"><?= htmlspecialchars($game['title']) ?></h2>

                            <?php if ((int)$game['review_count'] > 0): ?>
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?= $i <= round($game['avg_rating']) ? '⭐' : '☆' ?>
                                    <?php endfor; ?>
                                </div>

                                <p>
                                    <strong><?= htmlspecialchars(round($game['avg_rating'], 1)) ?>/5</strong>
                                    (<?= htmlspecialchars($game['review_count']) ?> avis)
                                </p>
                            <?php else: ?>
                                <p>Aucune note utilisateur</p>
                            <?php endif; ?>

                            <span class="badge"><?= !empty($game['genre']) ? htmlspecialchars($game['genre']) : 'Genre inconnu' ?></span>
                            <span class="badge"><?= !empty($game['platform']) ? htmlspecialchars($game['platform']) : 'Plateforme inconnue' ?></span>

                            <p>Année : <?= !empty($game['release_year']) ? htmlspecialchars($game['release_year']) : 'Non renseignée' ?></p>

                            <p>
                                Ventes mondiales :
                                <?= $game['global_sales'] !== null && $game['global_sales'] !== ''
                                    ? htmlspecialchars($game['global_sales']) . ' M'
                                    : 'Non renseignées' ?>
                            </p>

                            <p>
                                Score critique :
                                <?= $game['critic_score'] !== null && $game['critic_score'] !== ''
                                    ? htmlspecialchars($game['critic_score'])
                                    : 'Non renseigné' ?>
                            </p>

                            <a href="game.php?id=<?= $game['id'] ?>" class="btn btn-secondary">Voir détails</a>
                        </article>
                    <?php endforeach; ?>

                    <?php if (empty($games)): ?>
                        <div class="info-box">
                            <h2>Aucun résultat</h2>
                            <p>Aucun jeu ne correspond à ta recherche.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>