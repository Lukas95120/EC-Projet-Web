<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$verificationLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id
             FROM users
             WHERE email = ?
             OR username = ?'
        );
        $stmt->execute([$email, $username]);

        if ($stmt->fetch()) {
            $error = 'Cet email ou ce pseudo est déjà utilisé.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            $stmt = $pdo->prepare(
                'INSERT INTO users
                 (username, email, password, role, email_verified, email_verification_token)
                 VALUES (?, ?, ?, "user", 0, ?)'
            );

            $stmt->execute([
                $username,
                $email,
                $hashedPassword,
                $token
            ]);

            $verificationLink = 'http://' . $_SERVER['HTTP_HOST']
                . dirname($_SERVER['PHP_SELF'])
                . '/verify_email.php?token=' . urlencode($token);

            $subject = 'Validation de ton compte GameStats';
            $message = "Bienvenue sur GameStats.\n\n"
                . "Clique sur ce lien pour valider ton adresse email :\n"
                . $verificationLink;

            @mail($email, $subject, $message);

            $success = 'Compte créé. Tu dois maintenant valider ton adresse email avant de te connecter.';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-wrapper">
    <div class="auth-card">
        <h1>Inscription</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($verificationLink): ?>
            <div class="alert alert-success">
                <strong>Lien de test local :</strong><br>
                <a href="<?= htmlspecialchars($verificationLink) ?>">
                    <?= htmlspecialchars($verificationLink) ?>
                </a>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Pseudo</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button class="btn" type="submit">Créer mon compte</button>
        </form>

        <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>