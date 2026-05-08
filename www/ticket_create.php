<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/content_filter.php';
require_once 'includes/moderation.php';

requireLogin();

$error = '';
$success = '';
$maxOpenTickets = 3;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Requête invalide.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $platform = trim($_POST['platform'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $releaseYear = $_POST['release_year'] !== '' ? (int)$_POST['release_year'] : null;
        $publisher = trim($_POST['publisher'] ?? '');
        $globalSales = $_POST['global_sales'] !== '' ? $_POST['global_sales'] : null;
        $criticScore = $_POST['critic_score'] !== '' ? $_POST['critic_score'] : null;
        $userScore = $_POST['user_score'] !== '' ? $_POST['user_score'] : null;
        $imageUrl = trim($_POST['image_url'] ?? '');
        $sourceUrl = trim($_POST['source_url'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $userId = $_SESSION['user']['id'];

        $titleContentError = validateUserContent($title);
        $messageContentError = $message !== '' ? validateUserContent($message) : null;

        if ($title === '') {
            $error = 'Le titre du jeu est obligatoire.';
        } elseif ($titleContentError !== null) {
            logModerationAction($pdo, $userId, 'ticket_title', $title, $titleContentError);

            $error = $titleContentError;
            $_POST['title'] = '';
        } elseif ($messageContentError !== null) {
            logModerationAction($pdo, $userId, 'ticket_message', $message, $messageContentError);

            $error = $messageContentError;
            $_POST['message'] = '';
        } elseif ($releaseYear !== null && ($releaseYear < 1950 || $releaseYear > (int)date('Y') + 5)) {
            $error = 'L’année de sortie semble invalide.';
        } elseif ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $error = 'Le lien de l’image est invalide.';
        } elseif ($sourceUrl !== '' && !filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
            $error = 'Le lien source est invalide.';
        } else {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM tickets
                 WHERE user_id = ?
                 AND status = "pending"'
            );
            $stmt->execute([$userId]);

            $openTicketsCount = (int)$stmt->fetchColumn();

            if ($openTicketsCount >= $maxOpenTickets) {
                $error = 'Tu as déjà trop de tickets en attente. Attends qu’un ticket soit traité avant d’en créer un nouveau.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO tickets
                     (user_id, title, platform, genre, release_year, publisher, global_sales, critic_score, user_score, image_url, source_url, message)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );

                $stmt->execute([
                    $userId,
                    $title,
                    $platform,
                    $genre,
                    $releaseYear,
                    $publisher,
                    $globalSales,
                    $criticScore,
                    $userScore,
                    $imageUrl,
                    $sourceUrl,
                    $message
                ]);

                $success = 'Demande envoyée avec succès.';
                $_POST = [];
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Demander l’ajout d’un jeu</h1>

        <div class="info-box">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="ticket_create.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="form-group">
                    <label for="title">Titre du jeu *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="platform">Plateforme</label>
                    <input
                        type="text"
                        id="platform"
                        name="platform"
                        placeholder="Ex : PC, PS5, Switch"
                        value="<?= htmlspecialchars($_POST['platform'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="genre">Genre</label>
                    <input
                        type="text"
                        id="genre"
                        name="genre"
                        placeholder="Ex : RPG, Action, Sport"
                        value="<?= htmlspecialchars($_POST['genre'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="release_year">Année de sortie</label>
                    <input
                        type="number"
                        id="release_year"
                        name="release_year"
                        placeholder="Ex : 2024"
                        value="<?= htmlspecialchars($_POST['release_year'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="publisher">Éditeur</label>
                    <input
                        type="text"
                        id="publisher"
                        name="publisher"
                        placeholder="Ex : Nintendo, Ubisoft, Rockstar..."
                        value="<?= htmlspecialchars($_POST['publisher'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="global_sales">Ventes mondiales estimées</label>
                    <input
                        type="number"
                        step="0.01"
                        id="global_sales"
                        name="global_sales"
                        placeholder="Ex : 12.50"
                        value="<?= htmlspecialchars($_POST['global_sales'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="critic_score">Score critique estimé</label>
                    <input
                        type="number"
                        step="0.1"
                        min="0"
                        max="10"
                        id="critic_score"
                        name="critic_score"
                        placeholder="Ex : 8.7"
                        value="<?= htmlspecialchars($_POST['critic_score'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="user_score">Score utilisateur estimé</label>
                    <input
                        type="number"
                        step="0.1"
                        min="0"
                        max="10"
                        id="user_score"
                        name="user_score"
                        placeholder="Ex : 9.1"
                        value="<?= htmlspecialchars($_POST['user_score'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="image_url">URL de l’image du jeu</label>
                    <input
                        type="url"
                        id="image_url"
                        name="image_url"
                        placeholder="Ex : https://site.com/image.jpg"
                        value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>"
                    >
                    <p class="form-help">Ajoute une URL directe vers une image du jeu.</p>
                </div>

                <div class="form-group">
                    <label for="source_url">Lien source</label>
                    <input
                        type="url"
                        id="source_url"
                        name="source_url"
                        placeholder="Ex : page officielle, Wikipédia, Metacritic..."
                        value="<?= htmlspecialchars($_POST['source_url'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="message">Message complémentaire</label>
                    <textarea
                        id="message"
                        name="message"
                        placeholder="Pourquoi ce jeu devrait être ajouté ?"
                    ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <button class="btn" type="submit">Envoyer la demande</button>
                <a href="tickets.php" class="btn btn-secondary">Mes tickets</a>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>