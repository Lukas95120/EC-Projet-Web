document.addEventListener("DOMContentLoaded", () => {
    window.GameStats = window.GameStats || {};
    window.GameStats.basePath = document.body.dataset.basePath || "";

    window.GameStats.showToast = function (message, type = "success") {
        let container = document.querySelector(".toast-container");

        if (!container) {
            container = document.createElement("div");
            container.className = "toast-container";
            document.body.appendChild(container);
        }

        const toast = document.createElement("div");
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => toast.classList.add("toast-hide"), 2600);
        setTimeout(() => toast.remove(), 3100);
    };

    window.GameStats.clearInappropriateFields = function (form) {
        const input = form.querySelector("#comment, textarea[name='comment'], textarea");

        if (input) {
            input.value = "";
            input.textContent = "";
        }
    };

    window.GameStats.bindCardMouseEffect = function (scope = document) {
        scope.querySelectorAll(".game-card, .card").forEach((card) => {
            if (card.dataset.mouseBound === "true") return;

            card.dataset.mouseBound = "true";

            card.addEventListener("mousemove", (event) => {
                const rect = card.getBoundingClientRect();
                card.style.setProperty("--mouse-x", `${event.clientX - rect.left}px`);
                card.style.setProperty("--mouse-y", `${event.clientY - rect.top}px`);
            });
        });
    };

    const menuToggle = document.getElementById("menuToggle");
    const mainNav = document.getElementById("mainNav");

    if (menuToggle && mainNav) {
        menuToggle.addEventListener("click", () => {
            mainNav.classList.toggle("open");
        });
    }

    document.querySelectorAll(".alert").forEach((alert) => {
        setTimeout(() => alert.classList.add("alert-hide"), 3500);
        setTimeout(() => alert.remove(), 4300);
    });

    const avatarInput = document.getElementById("avatar");
    const fileName = document.getElementById("fileName");

    if (avatarInput && fileName) {
        avatarInput.addEventListener("change", () => {
            fileName.textContent = avatarInput.files.length > 0
                ? avatarInput.files[0].name
                : "Aucun fichier choisi";
        });
    }

    window.GameStats.bindCardMouseEffect();
});