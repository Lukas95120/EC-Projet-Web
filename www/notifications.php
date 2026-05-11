<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT *
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="admin-header">
            <div>
                <span class="eyebrow">Compte</span>
                <h1 class="section-title">Mes notifications</h1>
                    <form
                        action="notification_read_all.php"
                        method="POST"
                        id="readAllNotificationsForm"
                        class="notification-top-actions"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= generateCsrfToken() ?>"
                        >

                        <button class="btn btn-secondary" type="submit">
                            Tout marquer comme lu
                        </button>
                    </form>

                    <form
                        action="notification_delete_all.php"
                        method="POST"
                        id="deleteAllNotificationsForm"
                        class="notification-top-actions"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= generateCsrfToken() ?>"
                        >

                        <button class="btn btn-danger" type="submit">
                            Tout supprimer
                        </button>
                    </form>
            </div>

            <a href="profile.php" class="btn btn-secondary">Retour profil</a>
        </div>

        <div
            class="dashboard-grid"
            id="notificationsGrid"
            data-csrf-token="<?= generateCsrfToken() ?>"
        >
            <?php if (empty($notifications)): ?>
                <article class="info-box">
                    <h2>Aucune notification</h2>
                    <p>Tu n’as pas encore reçu de notification.</p>
                </article>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <article
                        class="info-box notification-card"
                        data-notification-id="<?= htmlspecialchars($notification['id']) ?>"
                    >
                        <span class="eyebrow">
                            <?= htmlspecialchars($notification['type']) ?>
                        </span>

                        <?php if ((int)$notification['is_read'] === 0): ?>
                            <span class="status-badge status-pending notification-status">
                                Non lue
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-added notification-status">
                                Lue
                            </span>
                        <?php endif; ?>

                        <h2><?= htmlspecialchars($notification['title']) ?></h2>

                        <p><?= nl2br(htmlspecialchars($notification['message'])) ?></p>

                        <p class="form-help">
                            <?= htmlspecialchars($notification['created_at']) ?>
                        </p>

                        <div class="notification-actions">
                            <?php if (!empty($notification['link'])): ?>
                                <a
                                    href="<?= htmlspecialchars($notification['link']) ?>"
                                    class="btn btn-secondary"
                                >
                                    Voir
                                </a>
                            <?php endif; ?>

                            <?php if ((int)$notification['is_read'] === 0): ?>
                                <form
                                    action="notification_read.php"
                                    method="POST"
                                    class="notification-read-form"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= generateCsrfToken() ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="notification_id"
                                        value="<?= htmlspecialchars($notification['id']) ?>"
                                    >

                                    <button class="btn btn-secondary" type="submit">
                                        Marquer comme lu
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form
                                action="notification_delete.php"
                                method="POST"
                                class="notification-delete-form"
                            >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= generateCsrfToken() ?>"
                                >

                                <input
                                    type="hidden"
                                    name="notification_id"
                                    value="<?= htmlspecialchars($notification['id']) ?>"
                                >

                                <button class="btn btn-danger" type="submit">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="confirm-modal" id="deleteNotificationsModal" aria-hidden="true">
    <div class="confirm-modal-backdrop" data-close-modal></div>

    <div class="confirm-modal-box">
        <h2>Supprimer toutes les notifications ?</h2>
        <p>Cette action est définitive. Toutes tes notifications seront supprimées.</p>

        <div class="confirm-modal-actions">
            <button class="btn btn-secondary" type="button" data-close-modal>
                Annuler
            </button>

            <button class="btn btn-danger" type="button" id="confirmDeleteAllNotifications">
                Supprimer
            </button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>