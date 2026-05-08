<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/badges.php';

requireLogin();

$userId = $_SESSION['user']['id'];
$error = '';
$success = '';

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Requête invalide.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $avatarName = $user['avatar'];

        if ($username === '') {
            $error = 'Le pseudo est obligatoire.';
        } else {
            if (!empty($_FILES['avatar']['name'])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $fileType = mime_content_type($_FILES['avatar']['tmp_name']);
                $fileSize = $_FILES['avatar']['size'];

                if (!in_array($fileType, $allowedTypes, true)) {
                    $error = 'Seuls les fichiers JPG, PNG et WEBP sont acceptés.';
                } elseif ($fileSize > 2 * 1024 * 1024) {
                    $error = 'L’image ne doit pas dépasser 2 Mo.';
                } else {
                    $extensions = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    $extension = $extensions[$fileType];
                    $avatarName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
                    $destination = 'assets/uploads/' . $avatarName;

                    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                        $error = 'Erreur lors du téléversement de l’image.';
                    }
                }
            }

            if ($error === '') {
                $stmt = $pdo->prepare(
                    'UPDATE users SET username = ?, bio = ?, avatar = ? WHERE id = ?'
                );
                $stmt->execute([$username, $bio, $avatarName, $userId]);

                $_SESSION['user']['username'] = $username;

                $success = 'Profil mis à jour avec succès.';

                $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            }
        }
    }
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM favorites WHERE user_id = ?');
$stmt->execute([$userId]);
$favoriteCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$stmt->execute([$userId]);
$reviewCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM tickets WHERE user_id = ?');
$stmt->execute([$userId]);
$ticketCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT AVG(rating) FROM reviews WHERE user_id = ?');
$stmt->execute([$userId]);
$averageGivenRating = $stmt->fetchColumn();
$averageGivenRating = $averageGivenRating ? round($averageGivenRating, 1) : null;

$userForBadges = $user;
$userForBadges['review_count'] = $reviewCount;
$userForBadges['favorite_count'] = $favoriteCount;
$userForBadges['ticket_count'] = $ticketCount;

$userBadges = getUserBadges($userForBadges);

$stmt = $pdo->prepare(
    'SELECT games.*
     FROM favorites
     INNER JOIN games ON favorites.game_id = games.id
     WHERE favorites.user_id = ?
     ORDER BY favorites.created_at DESC
     LIMIT 3'
);
$stmt->execute([$userId]);
$latestFavorites = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT reviews.*, games.title, games.id AS game_id
     FROM reviews
     INNER JOIN games ON reviews.game_id = games.id
     WHERE reviews.user_id = ?
     ORDER BY reviews.created_at DESC
     LIMIT 3'
);
$stmt->execute([$userId]);
$latestReviews = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Mon profil</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <aside class="info-box avatar-box">
                <?php if (!empty($user['avatar'])): ?>
                    <img
                        src="assets/uploads/<?= htmlspecialchars($user['avatar']) ?>"
                        alt="Avatar"
                        class="profile-avatar"
                    >
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?= htmlspecialchars(strtoupper(substr($user['username'], 0, 1))) ?>
                    </div>
                <?php endif; ?>

                <h2><?= htmlspecialchars($user['username']) ?></h2>
                <p>Membre GameStats</p>

                <div class="user-badges">
                    <?php foreach ($userBadges as $badge): ?>
                        <span class="user-badge <?= htmlspecialchars($badge['class']) ?>">
                            <?= htmlspecialchars($badge['icon']) ?>
                            <?= htmlspecialchars($badge['label']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="profile-bio-preview">
                    <h3>Bio</h3>
                    <p>
                        <?= !empty($user['bio'])
                            ? nl2br(htmlspecialchars($user['bio']))
                            : 'Aucune biographie renseignée.' ?>
                    </p>
                </div>
            </aside>

            <div class="info-box">
                <h2>Modifier mon profil</h2>

                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="form-group">
                        <label for="username">Pseudo</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars($user['username']) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="bio">Biographie</label>
                        <textarea
                            id="bio"
                            name="bio"
                            placeholder="Présente-toi en quelques mots..."
                        ><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="avatar">Avatar</label>

                        <div class="custom-file-upload">
                            <input type="file" id="avatar" name="avatar" accept="image/png, image/jpeg, image/webp">
                            <label for="avatar" class="file-button">Choisir une image</label>
                            <span id="fileName" class="file-name">Aucun fichier choisi</span>
                        </div>

                        <p class="form-help">Formats acceptés : JPG, PNG, WEBP — max 2 Mo.</p>
                    </div>

                    <button class="btn" type="submit">Enregistrer</button>
                </form>
            </div>
        </div>

        <div class="dashboard-stats" style="margin-top: 2rem;">
            <article class="stat-card">
                <span>❤️</span>
                <h3><?= htmlspecialchars($favoriteCount) ?></h3>
                <p>Favoris</p>
            </article>

            <article class="stat-card">
                <span>⭐</span>
                <h3><?= htmlspecialchars($reviewCount) ?></h3>
                <p>Avis publiés</p>
            </article>

            <article class="stat-card">
                <span>🎫</span>
                <h3><?= htmlspecialchars($ticketCount) ?></h3>
                <p>Tickets créés</p>
            </article>

            <article class="stat-card">
                <span>🎯</span>
                <h3><?= $averageGivenRating !== null ? htmlspecialchars($averageGivenRating) : '—' ?></h3>
                <p>Note moyenne donnée</p>
            </article>
        </div>

        <div class="dashboard-grid">
            <article class="info-box">
                <h2>Derniers favoris</h2>

                <?php if (empty($latestFavorites)): ?>
                    <p>Aucun favori pour le moment.</p>
                    <a href="games.php" class="btn">Explorer les jeux</a>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($latestFavorites as $favorite): ?>
                            <li>
                                <strong><?= htmlspecialchars($favorite['title']) ?></strong>
                                <span>
                                    <?= !empty($favorite['genre']) ? htmlspecialchars($favorite['genre']) : 'Genre inconnu' ?>
                                    ·
                                    <?= !empty($favorite['platform']) ? htmlspecialchars($favorite['platform']) : 'Plateforme inconnue' ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="favorites.php" class="btn">Voir mes favoris</a>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Derniers avis</h2>

                <?php if (empty($latestReviews)): ?>
                    <p>Tu n’as pas encore publié d’avis.</p>
                    <a href="games.php" class="btn">Découvrir les jeux</a>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($latestReviews as $review): ?>
                            <li>
                                <strong><?= htmlspecialchars($review['title']) ?></strong>
                                <span><?= htmlspecialchars($review['rating']) ?>/5</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="games.php" class="btn">Ajouter d’autres avis</a>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Activité</h2>
                <p>
                    Continue à noter des jeux, créer des tickets et ajouter tes favoris
                    pour débloquer davantage de badges GameStats.
                </p>
                <a href="games.php" class="btn btn-secondary">Retour au catalogue</a>
            </article>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>