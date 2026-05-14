<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

$email = trim($_GET['email'] ?? $_POST['email'] ?? '');
$code = trim($_POST['code'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($email === '' || $code === '') {
        $error = 'Veuillez renseigner votre email et le code reçu.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id
             FROM users
             WHERE email = ?
             AND email_verification_token = ?
             AND email_verified = 0
             LIMIT 1'
        );

        $stmt->execute([
            $email,
            $code
        ]);

        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Code invalide ou compte déjà vérifié.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET email_verified = 1,
                     email_verification_token = NULL,
                     email_verified_at = NOW()
                 WHERE id = ?'
            );

            $stmt->execute([$user['id']]);

            $success = 'Adresse email vérifiée avec succès. Tu peux maintenant te connecter.';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-wrapper">
    <div class="auth-card">
        <h1>Vérification email</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <a href="login.php" class="btn">Se connecter</a>
        <?php else: ?>
            <p>Entre le code à 6 chiffres reçu par email.</p>

            <form action="verify_email.php" method="POST">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="code">Code de validation</label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        placeholder="Ex : 123456"
                        required
                    >
                </div>

                <button class="btn" type="submit">Valider mon compte</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>