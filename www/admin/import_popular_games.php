<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/rawg_api.php';

requireAdmin();

$message = '';
$error = '';

$pagesToImport = max(1, min(5, (int)($_POST['pages'] ?? 1)));
$pageSize = 40;

$orderingOptions = [
    '-rating',
    '-metacritic',
    '-released',
    '-added',
    '-updated'
];

$genreOptions = [
    '',
    '&genres=action',
    '&genres=adventure',
    '&genres=role-playing-games-rpg',
    '&genres=shooter',
    '&genres=indie',
    '&genres=strategy',
    '&genres=sports',
    '&genres=racing',
    '&genres=simulation',
    '&genres=puzzle',
    '&genres=platformer'
];

$platformOptions = [
    '',
    '&platforms=4',
    '&platforms=187',
    '&platforms=186',
    '&platforms=7',
    '&platforms=18',
    '&platforms=1'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Requête invalide.';
    } else {
        $imported = 0;
        $skipped = 0;
        $blocked = 0;
        $failed = 0;

        for ($i = 1; $i <= $pagesToImport; $i++) {
            $randomPage = random_int(1, 250);
            $ordering = $orderingOptions[array_rand($orderingOptions)];
            $genre = $genreOptions[array_rand($genreOptions)];
            $platform = $platformOptions[array_rand($platformOptions)];

            $endpoint =
                'games?page=' . $randomPage .
                '&page_size=' . $pageSize .
                '&ordering=' . urlencode($ordering) .
                $genre .
                $platform;

            $data = rawgRequest($endpoint);

            if (!$data || empty($data['results'])) {
                $failed++;
                continue;
            }

            foreach ($data['results'] as $gameData) {
                $rawgId = (int)($gameData['id'] ?? 0);

                if ($rawgId <= 0) {
                    $failed++;
                    continue;
                }

                $stmt = $pdo->prepare(
                    'SELECT id
                     FROM games
                     WHERE rawg_id = ?
                     LIMIT 1'
                );
                $stmt->execute([$rawgId]);

                if ($stmt->fetch()) {
                    $skipped++;
                    continue;
                }

                $game = getRawgGame($rawgId);

                if (!$game) {
                    $failed++;
                    continue;
                }

                if (isBlockedRawgGame($game)) {
                    $blocked++;
                    continue;
                }

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

                $imported++;
            }
        }

        $message =
            $imported . ' jeu(x) importé(s) • ' .
            $skipped . ' doublon(s) ignoré(s) • ' .
            $blocked . ' jeu(x) bloqué(s) • ' .
            $failed . ' échec(s)';
    }
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container">

        <div class="admin-header">
            <div>
                <span class="eyebrow">RAWG</span>
                <h1 class="section-title">Import massif aléatoire</h1>
            </div>

            <a href="dashboard.php" class="btn btn-secondary">
                Retour
            </a>
        </div>

        <article class="info-box">

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

            <h2>Importer des jeux aléatoires</h2>

            <p>
                Cette page récupère des jeux depuis RAWG en variant automatiquement
                les pages, les genres, les plateformes et les tris.
            </p>

            <form method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= generateCsrfToken() ?>"
                >

                <div class="form-group">
                    <label for="pages">Nombre de pages à importer</label>

                    <select id="pages" name="pages">
                        <option value="1">1 page — environ 40 jeux</option>
                        <option value="2">2 pages — environ 80 jeux</option>
                        <option value="3">3 pages — environ 120 jeux</option>
                        <option value="4">4 pages — environ 160 jeux</option>
                        <option value="5">5 pages — environ 200 jeux</option>
                    </select>

                    <p class="form-help">
                        Plus le nombre est élevé, plus l’import peut prendre du temps.
                    </p>
                </div>

                <button class="btn" type="submit">
                    Lancer l’import aléatoire
                </button>
            </form>

        </article>

    </div>
</section>

<?php include '../includes/footer.php'; ?>