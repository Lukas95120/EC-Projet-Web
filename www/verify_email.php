<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

$token = $_GET['token'] ?? '';

if ($token === '') {
    $error = 'Lien de vérification invalide.';
} else {
    $stmt = $pdo->prepare(
        'SELECT id
         FROM users
         WHERE email_verification_token = ?
         AND email_verified = 0
         LIMIT 1'
    );
    $stmt->execute([$token]);

    $user = $stmt->fetch();

    if (!$user) {
        $error = 'Lien invalide ou compte déjà vérifié.';
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
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>