<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM games WHERE id = ?');
$stmt->execute([$id]);
$game = $stmt->fetch();

if (!$game) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Requête invalide.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $platform = trim($_POST['platform'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $releaseYear = $_POST['release_year'] !== '' ? $_POST['release_year'] : null;
        $publisher = trim($_POST['publisher'] ?? '');
        $globalSales = $_POST['global_sales'] !== '' ? $_POST['global_sales'] : null;
        $criticScore = $_POST['critic_score'] !== '' ? $_POST['critic_score'] : null;
        $userScore = $_POST['user_score'] !== '' ? $_POST['user_score'] : null;
        $imageUrl = trim($_POST['image_url'] ?? '');

        if ($title === '') {
            $error = 'Le titre est obligatoire.';
        } elseif ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $error = 'Le lien de l’image est invalide.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE games
                 SET title = ?,
                     platform = ?,
                     genre = ?,
                     release_year = ?,
                     publisher = ?,
                     global_sales = ?,
                     critic_score = ?,
                     user_score = ?,
                     image_url = ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $title,
                $platform,
                $genre,
                $releaseYear,
                $publisher,
                $globalSales,
                $criticScore,
                $userScore,
                $imageUrl,
                $id
            ]);

            setFlash('success', 'Jeu modifié avec succès.');
            header('Location: dashboard.php');
            exit;
        }
    }
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Modifier un jeu</h1>

        <div class="info-box">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="games_edit.php?id=<?= $game['id'] ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="form-group">
                    <label for="title">Titre *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($_POST['title'] ?? $game['title']) ?>"
                        required
                    >
                </div>

                <button class="btn btn-secondary" type="button" id="autoFillGame">
                    🔍 Remplir automatiquement
                </button>

                <p class="form-help" id="autoFillMessage"></p>

                <div class="form-group">
                    <label for="platform">Plateforme</label>
                    <input
                        type="text"
                        id="platform"
                        name="platform"
                        value="<?= htmlspecialchars($_POST['platform'] ?? ($game['platform'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="genre">Genre</label>
                    <input
                        type="text"
                        id="genre"
                        name="genre"
                        value="<?= htmlspecialchars($_POST['genre'] ?? ($game['genre'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="release_year">Année</label>
                    <input
                        type="number"
                        id="release_year"
                        name="release_year"
                        value="<?= htmlspecialchars($_POST['release_year'] ?? ($game['release_year'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="publisher">Éditeur</label>
                    <input
                        type="text"
                        id="publisher"
                        name="publisher"
                        value="<?= htmlspecialchars($_POST['publisher'] ?? ($game['publisher'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="global_sales">Ventes mondiales</label>
                    <input
                        type="number"
                        step="0.01"
                        id="global_sales"
                        name="global_sales"
                        value="<?= htmlspecialchars($_POST['global_sales'] ?? ($game['global_sales'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="critic_score">Score critique</label>
                    <input
                        type="number"
                        step="0.1"
                        min="0"
                        max="10"
                        id="critic_score"
                        name="critic_score"
                        value="<?= htmlspecialchars($_POST['critic_score'] ?? ($game['critic_score'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="user_score">Score utilisateur</label>
                    <input
                        type="number"
                        step="0.1"
                        min="0"
                        max="10"
                        id="user_score"
                        name="user_score"
                        value="<?= htmlspecialchars($_POST['user_score'] ?? ($game['user_score'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="image_url">Image du jeu</label>
                    <input
                        type="url"
                        id="image_url"
                        name="image_url"
                        value="<?= htmlspecialchars($_POST['image_url'] ?? ($game['image_url'] ?? '')) ?>"
                        placeholder="URL de l’image"
                    >
                </div>

                <div id="gameImagePreview" class="game-image-preview">
                    <?php
                    $previewImage = $_POST['image_url'] ?? ($game['image_url'] ?? '');
                    ?>

                    <?php if (!empty($previewImage)): ?>
                        <img src="<?= htmlspecialchars($previewImage) ?>" alt="Aperçu du jeu" loading="lazy">
                    <?php endif; ?>
                </div>

                <button class="btn" type="submit">Enregistrer</button>
                <a href="dashboard.php" class="btn btn-secondary">Retour</a>
            </form>
        </div>
    </div>
</section>

<script src="../assets/js/game-autofill.js"></script>

<?php include '../includes/footer.php'; ?>