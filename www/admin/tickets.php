<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$stmt = $pdo->query(
    'SELECT tickets.*, users.username, users.email
     FROM tickets
     INNER JOIN users ON tickets.user_id = users.id
     WHERE tickets.status = "pending"
     ORDER BY tickets.created_at DESC'
);
$tickets = $stmt->fetchAll();

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Tickets ouverts</h1>

        <div class="admin-actions" style="margin-bottom: 1.5rem;">
            <a href="tickets_to_add.php" class="btn btn-secondary">Tickets acceptés à ajouter</a>
            <a href="tickets_archived.php" class="btn btn-secondary">Tickets archivés</a>
            <a href="dashboard.php" class="btn btn-secondary">Retour admin</a>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="info-box">
                <h2>Aucun ticket ouvert</h2>
                <p>Aucune demande en attente pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Jeu demandé</th>
                            <th>Utilisateur</th>
                            <th>Infos jeu</th>
                            <th>Notes / ventes</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($ticket['title']) ?></strong><br>

                                    <?php if (!empty($ticket['message'])): ?>
                                        <?= nl2br(htmlspecialchars($ticket['message'])) ?><br>
                                    <?php endif; ?>

                                    <?php if (!empty($ticket['source_url'])): ?>
                                        <a
                                            href="<?= htmlspecialchars($ticket['source_url']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="ticket-source"
                                        >
                                            Voir la source ↗
                                        </a>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($ticket['username']) ?><br>
                                    <?= htmlspecialchars($ticket['email']) ?>
                                </td>

                                <td>
                                    <strong>Plateforme :</strong> <?= !empty($ticket['platform']) ? htmlspecialchars($ticket['platform']) : 'Non renseignée' ?><br>
                                    <strong>Genre :</strong> <?= !empty($ticket['genre']) ? htmlspecialchars($ticket['genre']) : 'Non renseigné' ?><br>
                                    <strong>Année :</strong> <?= !empty($ticket['release_year']) ? htmlspecialchars($ticket['release_year']) : 'Non renseignée' ?><br>
                                    <strong>Éditeur :</strong> <?= !empty($ticket['publisher']) ? htmlspecialchars($ticket['publisher']) : 'Non renseigné' ?>
                                </td>

                                <td>
                                    <strong>Ventes :</strong>
                                    <?= $ticket['global_sales'] !== null && $ticket['global_sales'] !== '' ? htmlspecialchars($ticket['global_sales']) . ' M' : 'Non renseignées' ?><br>

                                    <strong>Critique :</strong>
                                    <?= $ticket['critic_score'] !== null && $ticket['critic_score'] !== '' ? htmlspecialchars($ticket['critic_score']) : 'Non renseigné' ?><br>

                                    <strong>Utilisateur :</strong>
                                    <?= $ticket['user_score'] !== null && $ticket['user_score'] !== '' ? htmlspecialchars($ticket['user_score']) : 'Non renseigné' ?>
                                </td>

                                <td>
                                    <span class="status-badge status-pending">🟡 En attente</span>
                                </td>

                                <td class="admin-actions">
                                    <form action="ticket_update.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
                                        <input type="hidden" name="status" value="accepted">
                                        <button class="btn" type="submit">Accepter</button>
                                    </form>

                                    <form action="ticket_update.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="btn btn-danger" type="submit">Refuser</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>