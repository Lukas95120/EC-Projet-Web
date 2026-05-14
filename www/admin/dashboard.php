<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$games = $pdo->query('SELECT * FROM games ORDER BY id DESC')->fetchAll();

$totalGames = $pdo->query('SELECT COUNT(*) FROM games')->fetchColumn();
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalBannedUsers = $pdo->query('SELECT COUNT(*) FROM users WHERE is_banned = 1')->fetchColumn();
$totalReviews = $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$totalFavorites = $pdo->query('SELECT COUNT(*) FROM favorites')->fetchColumn();
$totalModerationLogs = $pdo->query('SELECT COUNT(*) FROM moderation_logs')->fetchColumn();
$totalAdminModerationLogs = $pdo->query('SELECT COUNT(*) FROM admin_moderation_logs')->fetchColumn();

$totalTickets = $pdo->query('SELECT COUNT(*) FROM tickets')->fetchColumn();
$pendingTickets = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'pending'")->fetchColumn();
$ticketsToAdd = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'accepted' AND added_to_catalog = 0")->fetchColumn();
$archivedTickets = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'rejected' OR added_to_catalog = 1")->fetchColumn();

$topGame = $pdo->query('SELECT * FROM games ORDER BY global_sales DESC LIMIT 1')->fetch();

$latestTickets = $pdo->query(
    'SELECT tickets.*, users.username
     FROM tickets
     INNER JOIN users ON tickets.user_id = users.id
     ORDER BY tickets.created_at DESC
     LIMIT 5'
)->fetchAll();

$latestReviews = $pdo->query(
    'SELECT reviews.*, users.username, games.title
     FROM reviews
     INNER JOIN users ON reviews.user_id = users.id
     INNER JOIN games ON reviews.game_id = games.id
     ORDER BY reviews.created_at DESC
     LIMIT 5'
)->fetchAll();

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Dashboard admin</h1>

        <div class="dashboard-hero info-box">
            <div>
                <h2>Bienvenue dans l’espace admin</h2>
                <p>Gère le catalogue, les tickets utilisateurs, les avis, les membres, la modération et les statistiques du site.</p>
            </div>

            <div class="admin-actions">
                <a href="games_create.php" class="btn">Ajouter un jeu</a>
                <a href="import_games.php" class="btn btn-secondary">Importer RAWG</a>
                <a href="import_popular_games.php" class="btn btn-secondary">
                    Import massif
                </a>
                <a href="users.php" class="btn btn-secondary">Membres</a>
                <a href="reviews.php" class="btn btn-secondary">Avis</a>
                <a href="forum_messages.php" class="btn btn-secondary">Messages forum</a>
                <a href="moderation_logs.php" class="btn btn-secondary">Logs modération</a>
                <a href="moderation_history.php" class="btn btn-secondary">Historique modération</a>
                <a href="tickets.php" class="btn btn-secondary">Tickets ouverts</a>
                <a href="tickets_to_add.php" class="btn btn-secondary">Jeux à ajouter</a>
                <a href="tickets_archived.php" class="btn btn-secondary">Archives</a>
            </div>
        </div>

        <div class="dashboard-stats">
            <article class="stat-card">
                <span>🎮</span>
                <h3><?= htmlspecialchars($totalGames) ?></h3>
                <p>Jeux</p>
            </article>

            <article class="stat-card">
                <span>👤</span>
                <h3><?= htmlspecialchars($totalUsers) ?></h3>
                <p>Utilisateurs</p>
            </article>

            <article class="stat-card">
                <span>🚫</span>
                <h3><?= htmlspecialchars($totalBannedUsers) ?></h3>
                <p>Bannis</p>
            </article>

            <article class="stat-card">
                <span>🛡️</span>
                <h3><?= htmlspecialchars($totalModerationLogs) ?></h3>
                <p>Logs modération</p>
            </article>

            <article class="stat-card">
                <span>📜</span>
                <h3><?= htmlspecialchars($totalAdminModerationLogs) ?></h3>
                <p>Actions admin</p>
            </article>

            <article class="stat-card">
                <span>⭐</span>
                <h3><?= htmlspecialchars($totalReviews) ?></h3>
                <p>Avis</p>
            </article>

            <article class="stat-card">
                <span>❤️</span>
                <h3><?= htmlspecialchars($totalFavorites) ?></h3>
                <p>Favoris</p>
            </article>

            <article class="stat-card">
                <span>⏳</span>
                <h3><?= htmlspecialchars($pendingTickets) ?></h3>
                <p>Tickets ouverts</p>
            </article>

            <article class="stat-card">
                <span>✅</span>
                <h3><?= htmlspecialchars($ticketsToAdd) ?></h3>
                <p>À ajouter</p>
            </article>

            <article class="stat-card">
                <span>🗃️</span>
                <h3><?= htmlspecialchars($archivedTickets) ?></h3>
                <p>Archivés</p>
            </article>

            <article class="stat-card">
                <span>🎫</span>
                <h3><?= htmlspecialchars($totalTickets) ?></h3>
                <p>Total tickets</p>
            </article>
        </div>

        <div class="dashboard-grid">
            <article class="info-box">
                <h2>Top vente</h2>

                <?php if ($topGame): ?>
                    <h3><?= htmlspecialchars($topGame['title']) ?></h3>
                    <p><?= htmlspecialchars($topGame['global_sales']) ?> M ventes</p>
                    <p><?= htmlspecialchars($topGame['platform']) ?> · <?= htmlspecialchars($topGame['genre']) ?></p>
                    <a href="../game.php?id=<?= $topGame['id'] ?>" class="btn">Voir la fiche</a>
                <?php else: ?>
                    <p>Aucun jeu disponible.</p>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Derniers tickets</h2>

                <?php if (empty($latestTickets)): ?>
                    <p>Aucun ticket récent.</p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($latestTickets as $ticket): ?>
                            <li>
                                <strong><?= htmlspecialchars($ticket['title']) ?></strong>
                                <span>
                                    <a href="../user.php?id=<?= htmlspecialchars($ticket['user_id']) ?>">
                                        <?= htmlspecialchars($ticket['username']) ?>
                                    </a> ·
                                    <?= htmlspecialchars($ticket['status']) ?>
                                    <?= (int)$ticket['added_to_catalog'] === 1 ? ' · ajouté au catalogue' : '' ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="tickets.php" class="btn">Gérer les tickets</a>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Derniers avis</h2>

                <?php if (empty($latestReviews)): ?>
                    <p>Aucun avis récent.</p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($latestReviews as $review): ?>
                            <li>
                                <strong><?= htmlspecialchars($review['title']) ?></strong>
                                <span>
                                    <a href="../user.php?id=<?= htmlspecialchars($review['user_id']) ?>">
                                        <?= htmlspecialchars($review['username']) ?>
                                    </a> ·
                                    <?= htmlspecialchars($review['rating']) ?>/5
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="reviews.php" class="btn">Gérer les avis</a>
                <?php endif; ?>
            </article>
        </div>

        <h2 class="section-title">Catalogue des jeux</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Genre</th>
                        <th>Plateforme</th>
                        <th>Année</th>
                        <th>Éditeur</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($games as $game): ?>
                        <tr>
                            <td><?= htmlspecialchars($game['title']) ?></td>
                            <td><?= htmlspecialchars($game['genre']) ?></td>
                            <td><?= htmlspecialchars($game['platform']) ?></td>
                            <td><?= htmlspecialchars($game['release_year']) ?></td>
                            <td><?= htmlspecialchars($game['publisher']) ?></td>
                            <td class="admin-actions">
                                <a href="games_edit.php?id=<?= $game['id'] ?>" class="btn btn-secondary">Modifier</a>
                                <form action="games_delete.php" method="POST" class="admin-delete-confirm-form">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="game_id" value="<?= htmlspecialchars($game['id']) ?>">

                                    <button class="btn btn-danger" type="submit">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($games)): ?>
                        <tr>
                            <td colspan="6">Aucun jeu enregistré.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>