<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/rawg_api.php';

requireAdmin();

$message = '';
$error = '';
$results = [];

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);

    if ($search !== '') {
        $results = searchRawgGames($search);

        if (empty($results)) {
            $error = 'Aucun jeu trouvé ou résultats bloqués automatiquement.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Requête invalide.';
    } else {
        $rawgId = (int)($_POST['rawg_id'] ?? 0);

        if ($rawgId <= 0) {
            $error = 'Jeu invalide.';
        } else {
            $stmt = $pdo->prepare(
                'SELECT id
                 FROM games
                 WHERE rawg_id = ?
                 LIMIT 1'
            );
            $stmt->execute([$rawgId]);

            if ($stmt->fetch()) {
                $error = 'Ce jeu existe déjà.';
            } else {
                $game = getRawgGame($rawgId);

                if (!$game) {
                    $error = 'Impossible de récupérer le jeu.';
                } elseif (isBlockedRawgGame($game)) {
                    $error = 'Jeu bloqué automatiquement car il semble contenir du contenu adulte ou NSFW.';
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO games (
                            rawg_id,
                            title,
                            platform,
                            genre,
                            release_year,
                            publisher,
                            critic_score,
                            user_score,
                            metacritic,
                            image_url,
                            background_image,
                            description
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );

                    $stmt->execute([
                        $rawgId,
                        $game['name'] ?? 'Jeu inconnu',
                        extractRawgPlatforms($game),
                        extractRawgGenres($game),
                        extractRawgReleaseYear($game),
                        extractRawgPublishers($game),
                        $game['rating'] ?? null,
                        $game['rating'] ?? null,
                        $game['metacritic'] ?? null,
                        $game['background_image'] ?? null,
                        $game['background_image'] ?? null,
                        strip_tags($game['description_raw'] ?? '')
                    ]);

                    $message = 'Jeu importé avec succès.';
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container">

        <div class="admin-header">
            <div>
                <span class="eyebrow">Administration</span>
                <h1 class="section-title">Importer des jeux RAWG</h1>
            </div>

            <a href="dashboard.php" class="btn btn-secondary">
                Retour
            </a>
        </div>

        <article class="info-box">
            <h2>Recherche RAWG</h2>

            <p>
                Recherche un jeu puis importe-le automatiquement
                dans le catalogue GameStats.
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="GET">
                <div class="form-group">
                    <label for="search">Rechercher un jeu</label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        placeholder="Exemple : Elden Ring"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                    >
                </div>

                <button class="btn" type="submit">
                    Rechercher
                </button>
            </form>
        </article>

        <?php if (!empty($results)): ?>
            <div class="games-grid" style="margin-top: 2rem;">
                <?php foreach ($results as $game): ?>
                    <article class="game-card">

                        <?php if (!empty($game['background_image'])): ?>
                            <img
                                src="<?= htmlspecialchars($game['background_image']) ?>"
                                alt="<?= htmlspecialchars($game['name']) ?>"
                                class="game-image"
                            >
                        <?php endif; ?>

                        <div class="game-card-content">
                            <h2><?= htmlspecialchars($game['name']) ?></h2>

                            <p>
                                <?= !empty($game['released'])
                                    ? htmlspecialchars($game['released'])
                                    : 'Date inconnue' ?>
                            </p>

                            <?php if (!empty($game['rating'])): ?>
                                <p>⭐ <?= htmlspecialchars($game['rating']) ?>/5</p>
                            <?php endif; ?>

                            <form method="POST">
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= generateCsrfToken() ?>"
                                >

                                <input
                                    type="hidden"
                                    name="rawg_id"
                                    value="<?= htmlspecialchars($game['id']) ?>"
                                >

                                <button class="btn" type="submit">
                                    Importer
                                </button>
                            </form>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include '../includes/footer.php'; ?>