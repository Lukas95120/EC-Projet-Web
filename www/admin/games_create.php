<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$error = '';
$ticket = null;
$ticketId = (int)($_GET['ticket_id'] ?? $_POST['ticket_id'] ?? 0);

if ($ticketId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
    $stmt->execute([$ticketId]);
    $ticket = $stmt->fetch();
}

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
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO games (title, platform, genre, release_year, publisher, global_sales, critic_score, user_score, image_url)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
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
                $imageUrl
            ]);

            if ($ticketId > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE tickets
                     SET status = "accepted", added_to_catalog = 1
                     WHERE id = ?'
                );
                $stmt->execute([$ticketId]);
            }

            setFlash('success', 'Jeu ajouté avec succès.');
            header('Location: dashboard.php');
            exit;
        }
    }
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Ajouter un jeu</h1>

        <div class="info-box">
            <?php if ($ticket): ?>
                <div class="alert alert-info">
                    Formulaire pré-rempli depuis le ticket :
                    <strong><?= htmlspecialchars($ticket['title']) ?></strong>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="games_create.php<?= $ticketId > 0 ? '?ticket_id=' . $ticketId : '' ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <?php if ($ticketId > 0): ?>
                    <input type="hidden" name="ticket_id" value="<?= $ticketId ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Titre *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($_POST['title'] ?? ($ticket['title'] ?? '')) ?>"
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
                        value="<?= htmlspecialchars($_POST['platform'] ?? ($ticket['platform'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="genre">Genre</label>
                    <input
                        type="text"
                        id="genre"
                        name="genre"
                        value="<?= htmlspecialchars($_POST['genre'] ?? ($ticket['genre'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="release_year">Année</label>
                    <input
                        type="number"
                        id="release_year"
                        name="release_year"
                        value="<?= htmlspecialchars($_POST['release_year'] ?? ($ticket['release_year'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="publisher">Éditeur</label>
                    <input
                        type="text"
                        id="publisher"
                        name="publisher"
                        value="<?= htmlspecialchars($_POST['publisher'] ?? ($ticket['publisher'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="global_sales">Ventes mondiales</label>
                    <input
                        type="number"
                        step="0.01"
                        id="global_sales"
                        name="global_sales"
                        value="<?= htmlspecialchars($_POST['global_sales'] ?? ($ticket['global_sales'] ?? '')) ?>"
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
                        value="<?= htmlspecialchars($_POST['critic_score'] ?? ($ticket['critic_score'] ?? '')) ?>"
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
                        value="<?= htmlspecialchars($_POST['user_score'] ?? ($ticket['user_score'] ?? '')) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="image_url">Image du jeu</label>
                    <input
                        type="url"
                        id="image_url"
                        name="image_url"
                        placeholder="URL de l’image"
                        value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>"
                    >
                </div>

                <div id="gameImagePreview" class="game-image-preview">
                    <?php if (!empty($_POST['image_url'])): ?>
                        <img src="<?= htmlspecialchars($_POST['image_url']) ?>" alt="Aperçu du jeu">
                    <?php endif; ?>
                </div>

                <?php if (!empty($ticket['source_url'])): ?>
                    <p class="form-help">
                        Source fournie :
                        <a href="<?= htmlspecialchars($ticket['source_url']) ?>" target="_blank" rel="noopener noreferrer">
                            ouvrir le lien
                        </a>
                    </p>
                <?php endif; ?>

                <button class="btn" type="submit">Ajouter</button>
                <a href="<?= $ticketId > 0 ? 'tickets_to_add.php' : 'dashboard.php' ?>" class="btn btn-secondary">Retour</a>
            </form>
        </div>
    </div>
</section>

<script src="../assets/js/game-autofill.js"></script>

<?php include '../includes/footer.php'; ?>