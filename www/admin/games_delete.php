<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM games WHERE id = ?');
$stmt->execute([$id]);
$game = $stmt->fetch();

if (!$game) {
    setFlash('error', 'Jeu introuvable.');
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Requête invalide.');
        header('Location: dashboard.php');
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM games WHERE id = ?');
    $stmt->execute([$id]);

    setFlash('success', 'Jeu supprimé avec succès.');

    header('Location: dashboard.php');
    exit;
}

include '../includes/header.php';
?>

<section class="auth-wrapper">
    <div class="auth-card">
        <h1>Supprimer le jeu</h1>

        <p>Es-tu sûr de vouloir supprimer :</p>
        <p><strong><?= htmlspecialchars($game['title']) ?></strong></p>

        <form action="games_delete.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" value="<?= $game['id'] ?>">

            <button class="btn btn-danger" type="submit">Oui, supprimer</button>
            <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</section>

<?php include '../includes/footer.php'; ?>