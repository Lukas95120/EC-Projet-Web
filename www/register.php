<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

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
             OR username = ?
             LIMIT 1'
        );
        $stmt->execute([$email, $username]);

        if ($stmt->fetch()) {
            $error = 'Cet email ou ce pseudo est déjà utilisé.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $verificationCode = (string)random_int(100000, 999999);

            $stmt = $pdo->prepare(
                'INSERT INTO users
                 (username, email, password, role, email_verified, email_verification_token)
                 VALUES (?, ?, ?, "user", 0, ?)'
            );

            $stmt->execute([
                $username,
                $email,
                $hashedPassword,
                $verificationCode
            ]);

            $subject = 'Code de validation GameStats';

            $message =
                "Bienvenue sur GameStats.\n\n" .
                "Voici ton code de validation : " . $verificationCode . "\n\n" .
                "Entre ce code sur la page de vérification pour activer ton compte.\n\n" .
                SITE_URL . "/verify_email.php?email=" . urlencode($email);

            $headers = "From: GameStats <" . MAIL_FROM . ">\r\n";
            $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            @mail($email, $subject, $message, $headers);

            header('Location: verify_email.php?email=' . urlencode($email));
            exit;
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