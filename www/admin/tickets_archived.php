<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$stmt = $pdo->query(
    'SELECT tickets.*, users.username, users.email
     FROM tickets
     INNER JOIN users ON tickets.user_id = users.id
     WHERE tickets.status = "rejected"
     OR tickets.added_to_catalog = 1
     ORDER BY tickets.created_at DESC'
);
$tickets = $stmt->fetchAll();

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Tickets archivés</h1>

        <div class="admin-actions" style="margin-bottom: 1.5rem;">
            <a href="tickets.php" class="btn btn-secondary">Tickets ouverts</a>
            <a href="tickets_to_add.php" class="btn btn-secondary">Tickets acceptés à ajouter</a>
            <a href="dashboard.php" class="btn btn-secondary">Retour admin</a>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="info-box">
                <h2>Aucune archive</h2>
                <p>Aucun ticket refusé ou déjà ajouté au catalogue.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($tickets as $ticket): ?>
                    <article class="card">
                        <h3><?= htmlspecialchars($ticket['title']) ?></h3>

                        <?php if ((int)$ticket['added_to_catalog'] === 1): ?>
                            <span class="status-badge status-added">🟢 Ajouté au catalogue</span>
                        <?php else: ?>
                            <span class="status-badge status-rejected">🔴 Refusé</span>
                        <?php endif; ?>

                        <p><strong>Utilisateur :</strong> <?= htmlspecialchars($ticket['username']) ?></p>
                        <p><strong>Plateforme :</strong> <?= !empty($ticket['platform']) ? htmlspecialchars($ticket['platform']) : 'Non renseignée' ?></p>
                        <p><strong>Genre :</strong> <?= !empty($ticket['genre']) ? htmlspecialchars($ticket['genre']) : 'Non renseigné' ?></p>

                        <?php if (!empty($ticket['message'])): ?>
                            <p><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
                        <?php endif; ?>

                        <p><strong>Créé le :</strong> <?= htmlspecialchars($ticket['created_at']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>