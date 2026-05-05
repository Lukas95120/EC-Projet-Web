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
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$email, $username]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            $error = 'Cet email ou ce pseudo est déjà utilisé.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password, role)
                 VALUES (?, ?, ?, "user")'
            );

            $stmt->execute([$username, $email, $hashedPassword]);

            $success = 'Compte créé avec succès. Tu peux maintenant te connecter.';
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