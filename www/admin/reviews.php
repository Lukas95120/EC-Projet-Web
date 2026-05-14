<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalReviews = (int)$pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$totalPages = max(1, (int)ceil($totalReviews / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$stmt = $pdo->prepare(
    'SELECT reviews.*, users.username, users.avatar, games.title
     FROM reviews
     INNER JOIN users ON reviews.user_id = users.id
     INNER JOIN games ON reviews.game_id = games.id
     ORDER BY reviews.created_at DESC
     LIMIT :limit OFFSET :offset'
);

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$reviews = $stmt->fetchAll();

function buildReviewsPageUrl(int $page): string
{
    return 'reviews.php?page=' . $page;
}

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

        <div id="adminAjaxContent">
            <p class="form-help" style="margin-bottom: 1.5rem;">
                <?= htmlspecialchars($totalReviews) ?> avis trouvé(s)
                — page <?= htmlspecialchars($page) ?> / <?= htmlspecialchars($totalPages) ?>
            </p>

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
                                    <form action="review_delete.php" method="POST" class="admin-delete-confirm-form">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id']) ?>">

                                        <textarea
                                            name="delete_reason"
                                            placeholder="Raison de suppression..."
                                            required
                                        ></textarea>

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

            <?php if ($totalPages > 1): ?>
                <div class="catalog-pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= htmlspecialchars(buildReviewsPageUrl($page - 1)) ?>" class="pagination-link">
                            Précédent
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a
                            href="<?= htmlspecialchars(buildReviewsPageUrl($i)) ?>"
                            class="pagination-link <?= $i === $page ? 'pagination-active' : '' ?>"
                        >
                            <?= htmlspecialchars($i) ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= htmlspecialchars(buildReviewsPageUrl($page + 1)) ?>" class="pagination-link">
                            Suivant
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>