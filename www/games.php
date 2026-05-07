<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$search = trim($_GET['search'] ?? '');
$genre = trim($_GET['genre'] ?? '');
$platform = trim($_GET['platform'] ?? '');
$year = trim($_GET['year'] ?? '');
$sort = $_GET['sort'] ?? 'title_asc';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

$userId = isLoggedIn() ? $_SESSION['user']['id'] : null;

function buildCatalogUrl(int $page): string
{
    $params = $_GET;
    $params['page'] = $page;

    return 'games.php?' . http_build_query($params);
}

/* ===== Requête de comptage ===== */

$countSql = 'SELECT COUNT(DISTINCT games.id)
             FROM games
             LEFT JOIN reviews ON games.id = reviews.game_id
             WHERE 1=1';

$countParams = [];

if ($search !== '') {
    $countSql .= ' AND games.title LIKE ?';
    $countParams[] = '%' . $search . '%';
}

if ($genre !== '') {
    $countSql .= ' AND games.genre = ?';
    $countParams[] = $genre;
}

if ($platform !== '') {
    $countSql .= ' AND games.platform = ?';
    $countParams[] = $platform;
}

if ($year !== '') {
    $countSql .= ' AND games.release_year = ?';
    $countParams[] = $year;
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalFilteredGames = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($totalFilteredGames / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

/* ===== Requête principale ===== */

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
$sql .= ' ORDER BY ' . $orderBy . ' LIMIT ' . $perPage . ' OFFSET ' . $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$games = $stmt->fetchAll();

/* ===== Données filtres / stats ===== */

$genres = $pdo->query(
    'SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL AND genre != "" ORDER BY genre'
)->fetchAll();

$platforms = $pdo->query(
    'SELECT DISTINCT platform FROM games WHERE platform IS NOT NULL AND platform != "" ORDER BY platform'
)->fetchAll();

$totalGames = (int)$pdo->query('SELECT COUNT(*) FROM games')->fetchColumn();
$totalReviews = (int)$pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();

$averageCatalogRating = $pdo->query('SELECT AVG(rating) FROM reviews')->fetchColumn();
$averageCatalogRating = $averageCatalogRating ? round($averageCatalogRating, 1) : null;

include 'includes/header.php';
?>

<section class="section catalog-section">
    <div class="container">
        <div class="catalog-hero info-box">
            <div class="catalog-hero-content">
                <span class="eyebrow">Catalogue GameStats</span>
                <h1 class="section-title">Catalogue des jeux</h1>
                <p>
                    Explore les jeux disponibles, filtre par genre, plateforme ou année,
                    puis ajoute tes coups de cœur directement à tes favoris.
                </p>
            </div>

            <div class="catalog-stats">
                <article>
                    <strong><?= htmlspecialchars($totalGames) ?></strong>
                    <span>jeux</span>
                </article>

                <article>
                    <strong><?= htmlspecialchars($totalReviews) ?></strong>
                    <span>avis</span>
                </article>

                <article>
                    <strong><?= $averageCatalogRating !== null ? htmlspecialchars($averageCatalogRating) . '/5' : '—' ?></strong>
                    <span>moyenne</span>
                </article>
            </div>
        </div>

        <div class="games-layout catalog-layout">
            <aside class="filters catalog-filters">
                <h2>Filtres</h2>
                <p class="form-help">Affiner la recherche dans le catalogue.</p>

                <form action="games.php" method="GET" id="catalogFilterForm">
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
                            <option value="">Tous les genres</option>
                            <?php foreach ($genres as $g): ?>
                                <option
                                    value="<?= htmlspecialchars($g['genre']) ?>"
                                    <?= $genre === $g['genre'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($g['genre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="platform">Plateforme</label>
                        <select id="platform" name="platform">
                            <option value="">Toutes les plateformes</option>
                            <?php foreach ($platforms as $p): ?>
                                <option
                                    value="<?= htmlspecialchars($p['platform']) ?>"
                                    <?= $platform === $p['platform'] ? 'selected' : '' ?>
                                >
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
                    <a href="games.php" class="btn btn-secondary" id="catalogResetButton">Réinitialiser</a>
                </form>
            </aside>

            <div id="catalogContent" class="catalog-content">
                <div class="catalog-results info-box">
                    <div>
                        <h2><?= htmlspecialchars($totalFilteredGames) ?> résultat(s)</h2>

                        <?php if ($totalFilteredGames > 0): ?>
                            <p>
                                Affichage de <?= htmlspecialchars($offset + 1) ?>
                                à <?= htmlspecialchars(min($offset + $perPage, $totalFilteredGames)) ?>
                                sur <?= htmlspecialchars($totalFilteredGames) ?> jeu(x).
                            </p>
                        <?php else: ?>
                            <p>Aucun jeu ne correspond à ta recherche.</p>
                        <?php endif; ?>
                    </div>

                    <a href="ticket_create.php" class="btn btn-secondary">Demander un jeu</a>
                </div>

                <?php if (empty($games)): ?>
                    <div class="info-box catalog-empty">
                        <h2>Aucun résultat</h2>
                        <p>Aucun jeu ne correspond à ta recherche.</p>
                        <a href="games.php" class="btn">Voir tout le catalogue</a>
                    </div>
                <?php else: ?>
                    <div class="games-grid catalog-grid">
                        <?php foreach ($games as $game): ?>
                            <?php $isFavorite = $userId && !empty($game['is_favorite']); ?>

                            <article class="game-card catalog-card" data-game-id="<?= $game['id'] ?>">
                                <div class="catalog-image-wrap">
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

                                    <?php if ($isFavorite): ?>
                                        <span class="favorite-badge catalog-favorite-badge">❤️ Favori</span>
                                    <?php endif; ?>
                                </div>

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
                                        <strong>
                                            <?= (int)$game['review_count'] > 0
                                                ? htmlspecialchars(round($game['avg_rating'], 1)) . '/5'
                                                : '—' ?>
                                        </strong>
                                        <span>Avis</span>
                                    </div>

                                    <div>
                                        <strong>
                                            <?= $game['critic_score'] !== null && $game['critic_score'] !== ''
                                                ? htmlspecialchars($game['critic_score'])
                                                : '—' ?>
                                        </strong>
                                        <span>Critique</span>
                                    </div>

                                    <div>
                                        <strong>
                                            <?= !empty($game['release_year'])
                                                ? htmlspecialchars($game['release_year'])
                                                : '—' ?>
                                        </strong>
                                        <span>Année</span>
                                    </div>
                                </div>

                                <?php if ((int)$game['review_count'] > 0): ?>
                                    <div class="review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?= $i <= round($game['avg_rating']) ? '⭐' : '☆' ?>
                                        <?php endfor; ?>
                                    </div>

                                    <p><?= htmlspecialchars($game['review_count']) ?> avis utilisateur(s)</p>
                                <?php else: ?>
                                    <p>Aucune note utilisateur</p>
                                <?php endif; ?>

                                <p>
                                    Ventes mondiales :
                                    <?= $game['global_sales'] !== null && $game['global_sales'] !== ''
                                        ? htmlspecialchars($game['global_sales']) . ' M'
                                        : 'Non renseignées' ?>
                                </p>

                                <div class="catalog-actions">
                                    <a
                                        href="game.php?id=<?= $game['id'] ?>"
                                        class="btn btn-secondary catalog-detail-link"
                                    >
                                        Voir détails
                                    </a>

                                    <?php if (isLoggedIn()): ?>
                                        <form
                                            action="<?= $isFavorite ? 'remove_favorite.php' : 'add_favorite.php' ?>"
                                            method="POST"
                                            class="favorite-form"
                                            data-add-action="add_favorite.php"
                                            data-remove-action="remove_favorite.php"
                                        >
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                            <input type="hidden" name="return" value="games.php">

                                            <button
                                                class="btn <?= $isFavorite ? 'btn-danger' : '' ?>"
                                                type="submit"
                                                data-favorite-button
                                            >
                                                <?= $isFavorite ? 'Retirer' : 'Favori' ?>
                                            </button>

                                            <p class="form-help favorite-message" data-favorite-message></p>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav class="catalog-pagination">
                            <?php if ($page > 1): ?>
                                <a href="<?= htmlspecialchars(buildCatalogUrl($page - 1)) ?>" class="pagination-link">
                                    ← Précédent
                                </a>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a
                                    href="<?= htmlspecialchars(buildCatalogUrl($i)) ?>"
                                    class="pagination-link <?= $i === $page ? 'pagination-active' : '' ?>"
                                >
                                    <?= htmlspecialchars($i) ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="<?= htmlspecialchars(buildCatalogUrl($page + 1)) ?>" class="pagination-link">
                                    Suivant →
                                </a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>