<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">

        <div class="admin-header">
            <div>
                <span class="eyebrow">Communauté</span>
                <h1 class="section-title">Découvrir la communauté</h1>
            </div>

            <a href="friends.php" class="btn btn-secondary">
                Retour amis
            </a>
        </div>

        <div class="info-box" style="margin-bottom: 2rem;">
            <h2>Rechercher un membre</h2>

            <div class="form-group">
                <label for="communitySearch">Pseudo</label>
                <input
                    type="text"
                    id="communitySearch"
                    placeholder="Ex : lukas"
                    autocomplete="off"
                >
            </div>

            <p class="form-help" id="communitySearchStatus">
                Les membres apparaissent automatiquement pendant la recherche.
            </p>
        </div>

        <div
            class="admin-users-grid"
            id="communityResults"
        >
            <article class="info-box">
                <h2>Chargement...</h2>
                <p>Recherche des membres disponibles.</p>
            </article>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>