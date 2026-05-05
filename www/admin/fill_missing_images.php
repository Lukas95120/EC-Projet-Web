<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/config.php';

requireAdmin();

$error = '';
$success = '';
$updatedGames = [];

if (!defined('RAWG_API_KEY') || RAWG_API_KEY === '' || RAWG_API_KEY === 'TA_CLE_API_RAWG_ICI') {
    $error = 'Clé API RAWG manquante dans config.php.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Requête invalide.';
    } else {
        $stmt = $pdo->query(
            'SELECT id, title
             FROM games
             WHERE image_url IS NULL OR image_url = ""
             ORDER BY id ASC
             LIMIT 25'
        );
        $games = $stmt->fetchAll();

        foreach ($games as $game) {
            $url = 'https://api.rawg.io/api/games?key=' . urlencode(RAWG_API_KEY)
                . '&search=' . urlencode($game['title'])
                . '&page_size=1';

            $response = @file_get_contents($url);

            if ($response === false) {
                continue;
            }

            $data = json_decode($response, true);
            $imageUrl = $data['results'][0]['background_image'] ?? '';

            if ($imageUrl !== '') {
                $stmt = $pdo->prepare('UPDATE games SET image_url = ? WHERE id = ?');
                $stmt->execute([$imageUrl, $game['id']]);

                $updatedGames[] = $game['title'];
            }
        }

        if (!empty($updatedGames)) {
            $success = count($updatedGames) . ' image(s) ajoutée(s) avec succès.';
        } else {
            $success = 'Aucune image trouvée pour les jeux sans image.';
        }
    }
}

$stmt = $pdo->query(
    'SELECT COUNT(*)
     FROM games
     WHERE image_url IS NULL OR image_url = ""'
);
$missingCount = (int)$stmt->fetchColumn();

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Remplir les images manquantes</h1>

        <div class="info-box">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <h2><?= htmlspecialchars($missingCount) ?> jeu(x) sans image</h2>
            <p>
                Cette action recherche automatiquement une image RAWG pour les anciens jeux.
                Pour éviter trop de requêtes, le script traite 25 jeux maximum par clic.
            </p>

            <?php if (!empty($updatedGames)): ?>
                <ul class="dashboard-list">
                    <?php foreach ($updatedGames as $title): ?>
                        <li>
                            <strong><?= htmlspecialchars($title) ?></strong>
                            <span>Image ajoutée</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($missingCount > 0): ?>
                <form action="fill_missing_images.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <button class="btn" type="submit">Remplir automatiquement</button>
                    <a href="dashboard.php" class="btn btn-secondary">Retour</a>
                </form>
            <?php else: ?>
                <p>Tous les jeux ont déjà une image.</p>
                <a href="dashboard.php" class="btn btn-secondary">Retour</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>