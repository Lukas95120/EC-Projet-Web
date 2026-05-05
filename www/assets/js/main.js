document.addEventListener("DOMContentLoaded", () => {

    // ===== MENU MOBILE =====
    const menuToggle = document.getElementById("menuToggle");
    const mainNav = document.getElementById("mainNav");

    if (menuToggle && mainNav) {
        menuToggle.addEventListener("click", () => {
            mainNav.classList.toggle("open");
        });
    }

    // ===== ALERTES AUTO DISPARITION PREMIUM =====
    const alerts = document.querySelectorAll(".alert");

    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.classList.add("alert-hide");
        }, 3500);

        setTimeout(() => {
            alert.remove();
        }, 4300);
    });

    // ===== NOM FICHIER AVATAR =====
    const avatarInput = document.getElementById("avatar");
    const fileName = document.getElementById("fileName");

    if (avatarInput && fileName) {
        avatarInput.addEventListener("change", () => {
            fileName.textContent = avatarInput.files.length > 0
                ? avatarInput.files[0].name
                : "Aucun fichier choisi";
        });
    }

    // ===== EFFET SOURIS SUR CARTES =====
    const cards = document.querySelectorAll(".game-card, .card");

    cards.forEach((card) => {
        card.addEventListener("mousemove", (event) => {
            const rect = card.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            card.style.setProperty("--mouse-x", `${x}px`);
            card.style.setProperty("--mouse-y", `${y}px`);
        });
    });

});