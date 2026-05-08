<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$stmt = $pdo->query(
    'SELECT reviews.*, users.username, users.avatar, games.title
     FROM reviews
     INNER JOIN users ON reviews.user_id = users.id
     INNER JOIN games ON reviews.game_id = games.id
     ORDER BY reviews.created_at DESC'
);

$reviews = $stmt->fetchAll();

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="admin-header">
            <div>
                <span class="eyebrow">Administration</span>
                <h1 class="section-title">Gestion des avis</h1>
            </div>

            <a href="dashboard.php" class="btn btn-secondary">Retour dashboard</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Jeu</th>
                        <th>Utilisateur</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td>
                                <a href="../game.php?id=<?= htmlspecialchars($review['game_id']) ?>">
                                    <?= htmlspecialchars($review['title']) ?>
                                </a>
                            </td>

                            <td>
                                <a
                                    href="../user.php?id=<?= htmlspecialchars($review['user_id']) ?>"
                                    class="mini-user-link"
                                >
                                    <span class="mini-user-avatar">
                                        <?php if (!empty($review['avatar'])): ?>
                                            <img
                                                src="../assets/uploads/<?= htmlspecialchars(basename($review['avatar'])) ?>"
                                                alt="<?= htmlspecialchars($review['username']) ?>"
                                            >
                                        <?php else: ?>
                                            <?= htmlspecialchars(strtoupper(substr($review['username'], 0, 1))) ?>
                                        <?php endif; ?>
                                    </span>

                                    <strong><?= htmlspecialchars($review['username']) ?></strong>
                                </a>
                            </td>

                            <td><?= htmlspecialchars($review['rating']) ?>/5</td>
                            <td><?= nl2br(htmlspecialchars($review['comment'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($review['created_at']) ?></td>

                            <td>
                                <form action="review_delete.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id']) ?>">
                                    <button class="btn btn-danger" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="6">Aucun avis trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>