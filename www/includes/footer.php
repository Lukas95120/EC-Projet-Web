<?php
$isAdminPage = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$basePath = $isAdminPage ? '../' : '';
?>

</main>

<footer class="site-footer">
    <div class="container">
        <p>&copy; 2026 GameStats — Projet web EC 2025-2026</p>
        <p>Données issues d’un dataset public Kaggle.</p>
    </div>
</footer>

<script src="<?= $basePath ?>assets/js/main.js"></script>
<script src="<?= $basePath ?>assets/js/catalog.js"></script>
<script src="<?= $basePath ?>assets/js/reviews.js"></script>
<script src="<?= $basePath ?>assets/js/forum.js"></script>
<script src="<?= $basePath ?>assets/js/notifications.js"></script>
<script src="<?= $basePath ?>assets/js/messages.js"></script>
<script src="<?= $basePath ?>assets/js/admin.js"></script>

<div class="confirm-modal" id="adminDeleteModal" aria-hidden="true">
    <div class="confirm-modal-backdrop" data-close-admin-delete-modal></div>

    <div class="confirm-modal-box">
        <h2>Confirmer la suppression ?</h2>
        <p>Cette action est définitive. Le contenu sera supprimé et l’utilisateur recevra une notification.</p>

        <div class="confirm-modal-actions">
            <button class="btn btn-secondary" type="button" data-close-admin-delete-modal>
                Annuler
            </button>

            <button class="btn btn-danger" type="button" id="confirmAdminDeleteButton">
                Supprimer
            </button>
        </div>
    </div>
</div>

<div class="confirm-modal" id="adminUserStatusModal" aria-hidden="true">
    <div class="confirm-modal-backdrop" data-close-user-status-modal></div>

    <div class="confirm-modal-box">
        <h2>Confirmer l’action ?</h2>
        <p>Cette action modifiera l’état du compte utilisateur.</p>

        <div class="confirm-modal-actions">
            <button class="btn btn-secondary" type="button" data-close-user-status-modal>
                Annuler
            </button>

            <button class="btn btn-danger" type="button" id="confirmUserStatusButton">
                Confirmer
            </button>
        </div>
    </div>
</div>

</body>
</html>