<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$stmt = $pdo->query(
    'SELECT tickets.*, users.username, users.email
     FROM tickets
     INNER JOIN users ON tickets.user_id = users.id
     WHERE tickets.status = "accepted"
     AND tickets.added_to_catalog = 0
     ORDER BY tickets.created_at DESC'
);
$tickets = $stmt->fetchAll();

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Tickets acceptés à ajouter</h1>

        <div class="admin-actions" style="margin-bottom: 1.5rem;">
            <a href="tickets.php" class="btn btn-secondary">Tickets ouverts</a>
            <a href="tickets_archived.php" class="btn btn-secondary">Tickets archivés</a>
            <a href="dashboard.php" class="btn btn-secondary">Retour admin</a>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="info-box">
                <h2>Aucun jeu à ajouter</h2>
                <p>Aucun ticket accepté n’attend l’ajout au catalogue.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($tickets as $ticket): ?>
                    <article class="card">
                        <h3><?= htmlspecialchars($ticket['title']) ?></h3>

                        <span class="status-badge status-accepted">🔵 Accepté — à ajouter</span>

                        <p><strong>Demandé par :</strong> <?= htmlspecialchars($ticket['username']) ?></p>
                        <p><strong>Plateforme :</strong> <?= !empty($ticket['platform']) ? htmlspecialchars($ticket['platform']) : 'Non renseignée' ?></p>
                        <p><strong>Genre :</strong> <?= !empty($ticket['genre']) ? htmlspecialchars($ticket['genre']) : 'Non renseigné' ?></p>
                        <p><strong>Année :</strong> <?= !empty($ticket['release_year']) ? htmlspecialchars($ticket['release_year']) : 'Non renseignée' ?></p>
                        <p><strong>Éditeur :</strong> <?= !empty($ticket['publisher']) ? htmlspecialchars($ticket['publisher']) : 'Non renseigné' ?></p>

                        <?php if (!empty($ticket['message'])): ?>
                            <p><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ticket['source_url'])): ?>
                            <p>
                                <a
                                    href="<?= htmlspecialchars($ticket['source_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="ticket-source"
                                >
                                    Voir la source ↗
                                </a>
                            </p>
                        <?php endif; ?>

                        <a href="games_create.php?ticket_id=<?= $ticket['id'] ?>" class="btn">
                            Ajouter ce jeu
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>