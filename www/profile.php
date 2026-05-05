<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

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
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <h2><?= htmlspecialchars($user['username']) ?></h2>
                <p>Membre GameStats</p>

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
    </div>
</section>

<?php include 'includes/footer.php'; ?>