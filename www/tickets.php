<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT *
     FROM tickets
     WHERE user_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([$userId]);
$tickets = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Mes tickets</h1>

        <a href="ticket_create.php" class="btn">Nouvelle demande</a>

        <?php if (empty($tickets)): ?>
            <div class="info-box" style="margin-top: 1.5rem;">
                <h2>Aucun ticket</h2>
                <p>Tu n’as pas encore demandé l’ajout d’un jeu.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid" style="margin-top: 1.5rem;">
                <?php foreach ($tickets as $ticket): ?>
                    <article class="card">
                        <h3><?= htmlspecialchars($ticket['title']) ?></h3>

                        <?php
                        $statusClass = 'status-pending';
                        $statusText = '🟡 En attente';

                        if ($ticket['status'] === 'accepted' && (int)$ticket['added_to_catalog'] === 0) {
                            $statusClass = 'status-accepted';
                            $statusText = '🔵 Accepté — en attente d’ajout';
                        }

                        if ((int)$ticket['added_to_catalog'] === 1) {
                            $statusClass = 'status-added';
                            $statusText = '🟢 Ajouté au catalogue';
                        }

                        if ($ticket['status'] === 'rejected') {
                            $statusClass = 'status-rejected';
                            $statusText = '🔴 Refusé';
                        }
                        ?>

                        <span class="status-badge <?= $statusClass ?>">
                            <?= $statusText ?>
                        </span>

                        <?php if (!empty($ticket['platform'])): ?>
                            <p><strong>Plateforme :</strong> <?= htmlspecialchars($ticket['platform']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ticket['genre'])): ?>
                            <p><strong>Genre :</strong> <?= htmlspecialchars($ticket['genre']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ticket['release_year'])): ?>
                            <p><strong>Année :</strong> <?= htmlspecialchars($ticket['release_year']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ticket['publisher'])): ?>
                            <p><strong>Éditeur :</strong> <?= htmlspecialchars($ticket['publisher']) ?></p>
                        <?php endif; ?>

                        <?php if ($ticket['global_sales'] !== null && $ticket['global_sales'] !== ''): ?>
                            <p><strong>Ventes :</strong> <?= htmlspecialchars($ticket['global_sales']) ?> M</p>
                        <?php endif; ?>

                        <?php if ($ticket['critic_score'] !== null && $ticket['critic_score'] !== ''): ?>
                            <p><strong>Score critique :</strong> <?= htmlspecialchars($ticket['critic_score']) ?></p>
                        <?php endif; ?>

                        <?php if ($ticket['user_score'] !== null && $ticket['user_score'] !== ''): ?>
                            <p><strong>Score utilisateur :</strong> <?= htmlspecialchars($ticket['user_score']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($ticket['source_url'])): ?>
                            <p>
                                <strong>Source :</strong>
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

                        <?php if (!empty($ticket['message'])): ?>
                            <p><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
                        <?php endif; ?>

                        <p class="ticket-meta">
                            <strong>Créé le :</strong> <?= htmlspecialchars($ticket['created_at']) ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>