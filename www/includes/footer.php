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
</body>
</html>