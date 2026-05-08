<?php
require_once __DIR__ . '/auth.php';

$isAdminPage = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$basePath = $isAdminPage ? '../' : '';
$flash = getFlash();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameStats</title>

    <?php if (isLoggedIn()): ?>
        <meta
            name="current-user-id"
            content="<?= htmlspecialchars($_SESSION['user']['id']) ?>"
        >
    <?php endif; ?>

    <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-content">
        <a href="<?= htmlspecialchars($basePath) ?>index.php" class="logo">GameStats</a>

        <button class="menu-toggle" id="menuToggle" aria-label="Ouvrir le menu">
            ☰
        </button>

        <nav class="main-nav" id="mainNav">
            <a href="<?= htmlspecialchars($basePath) ?>index.php">Accueil</a>
            <a href="<?= htmlspecialchars($basePath) ?>games.php">Jeux</a>

            <?php if (isLoggedIn()): ?>
                <a href="<?= htmlspecialchars($basePath) ?>favorites.php">Favoris</a>
                <a href="<?= htmlspecialchars($basePath) ?>tickets.php">Tickets</a>
                <a href="<?= htmlspecialchars($basePath) ?>forum.php">Forum</a>
                <a href="<?= htmlspecialchars($basePath) ?>profile.php">Profil</a>
            <?php endif; ?>

            <?php if (isAdmin()): ?>
                <a href="<?= htmlspecialchars($basePath) ?>admin/dashboard.php">Admin</a>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <a href="<?= htmlspecialchars($basePath) ?>logout.php" class="btn-nav">Déconnexion</a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($basePath) ?>login.php" class="btn-nav">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>

<?php if ($flash): ?>
    <div class="flash-wrapper">
        <div class="container">
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        </div>
    </div>
<?php endif; ?>