<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$userId = $_SESSION['user']['id'];

function formatSanctionAction(string $action): string
{
    return match ($action) {
        'ban_user' => 'Bannissement',
        'unban_user' => 'Débannissement',
        'delete_review' => 'Avis supprimé',
        'delete_forum_message' => 'Message forum supprimé',
        default => $action
    };
}

$stmt = $pdo->prepare(
    'SELECT *
     FROM admin_moderation_logs
     WHERE target_user_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([$userId]);
$adminSanctions = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT *
     FROM moderation_logs
     WHERE user_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([$userId]);
$blockedContents = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="admin-header">
            <div>
                <span class="eyebrow">Compte</span>
                <h1 class="section-title">Mes sanctions</h1>
            </div>

            <a href="profile.php" class="btn btn-secondary">Retour profil</a>
        </div>

        <div class="dashboard-grid">
            <article class="info-box">
                <h2>Actions de modération</h2>

                <?php if (empty($adminSanctions)): ?>
                    <p>Aucune sanction administrateur enregistrée.</p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($adminSanctions as $sanction): ?>
                            <li>
                                <strong><?= htmlspecialchars(formatSanctionAction($sanction['action'])) ?></strong>

                                <span>
                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($sanction['created_at']))) ?>
                                </span>

                                <p>
                                    <strong>Raison :</strong>
                                    <?= !empty($sanction['reason'])
                                        ? nl2br(htmlspecialchars($sanction['reason']))
                                        : 'Aucune raison précisée.' ?>
                                </p>

                                <?php if (!empty($sanction['details'])): ?>
                                    <p>
                                        <strong>Détails :</strong>
                                        <?= nl2br(htmlspecialchars($sanction['details'])) ?>
                                    </p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Contenus bloqués automatiquement</h2>

                <?php if (empty($blockedContents)): ?>
                    <p>Aucun contenu bloqué automatiquement.</p>
                <?php else: ?>
                    <ul class="dashboard-list">
                        <?php foreach ($blockedContents as $content): ?>
                            <li>
                                <strong><?= htmlspecialchars($content['content_type']) ?></strong>

                                <span>
                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($content['created_at']))) ?>
                                </span>

                                <p>
                                    <strong>Raison :</strong>
                                    <?= htmlspecialchars($content['reason']) ?>
                                </p>

                                <p>
                                    <strong>Contenu :</strong>
                                    <?= nl2br(htmlspecialchars($content['blocked_content'])) ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="info-box">
                <h2>Informations</h2>
                <p>
                    Cette page te permet de consulter les actions de modération liées à ton compte,
                    ainsi que les contenus automatiquement bloqués par le système.
                </p>

                <a href="notifications.php" class="btn btn-secondary">
                    Voir mes notifications
                </a>
            </article>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>