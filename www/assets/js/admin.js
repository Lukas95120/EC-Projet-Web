document.addEventListener("DOMContentLoaded", () => {
    const showToast = window.GameStats.showToast;

    let pendingAdminDeleteForm = null;
    let pendingUserStatusForm = null;

    async function loadAdminAjaxPage(url, updateHistory = true) {
        const container = document.getElementById("adminAjaxContent");

        if (!container) {
            window.location.href = url;
            return;
        }

        container.classList.add("catalog-loading");

        try {
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newContent = doc.getElementById("adminAjaxContent");

            if (!newContent) {
                throw new Error("Contenu admin introuvable.");
            }

            container.innerHTML = newContent.innerHTML;

            if (updateHistory) {
                window.history.pushState({}, "", url);
            }

            bindAdminConfirmations();

        } catch (error) {
            showToast("Erreur pendant le chargement admin.", "error");
        }

        container.classList.remove("catalog-loading");
    }

    async function submitAdminPostForm(form) {
        const formData = new FormData(form);

        try {
            await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });

            await loadAdminAjaxPage(window.location.href, false);
            showToast("Action effectuée avec succès.", "success");

        } catch (error) {
            showToast("Erreur pendant l’action admin.", "error");
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);

        if (modal) {
            modal.classList.remove("open");
            modal.setAttribute("aria-hidden", "true");
        }
    }

    function bindAdminConfirmations() {
        document.querySelectorAll(".admin-delete-confirm-form").forEach((form) => {
            if (form.dataset.bound === "true") return;

            form.dataset.bound = "true";

            form.addEventListener("submit", (event) => {
                event.preventDefault();

                pendingAdminDeleteForm = form;

                const modal = document.getElementById("adminDeleteModal");

                if (modal) {
                    modal.classList.add("open");
                    modal.setAttribute("aria-hidden", "false");
                }
            });
        });

        document.querySelectorAll(".admin-user-status-confirm-form").forEach((form) => {
            if (form.dataset.bound === "true") return;

            form.dataset.bound = "true";

            form.addEventListener("submit", (event) => {
                event.preventDefault();

                pendingUserStatusForm = form;

                const modal = document.getElementById("adminUserStatusModal");

                if (modal) {
                    modal.classList.add("open");
                    modal.setAttribute("aria-hidden", "false");
                }
            });
        });
    }

    const confirmAdminDeleteButton = document.getElementById("confirmAdminDeleteButton");

    if (confirmAdminDeleteButton) {
        confirmAdminDeleteButton.addEventListener("click", async () => {
            if (!pendingAdminDeleteForm) return;

            const form = pendingAdminDeleteForm;
            pendingAdminDeleteForm = null;

            closeModal("adminDeleteModal");

            await submitAdminPostForm(form);
        });
    }

    const confirmUserStatusButton = document.getElementById("confirmUserStatusButton");

    if (confirmUserStatusButton) {
        confirmUserStatusButton.addEventListener("click", async () => {
            if (!pendingUserStatusForm) return;

            const form = pendingUserStatusForm;
            pendingUserStatusForm = null;

            closeModal("adminUserStatusModal");

            await submitAdminPostForm(form);
        });
    }

    document.querySelectorAll("[data-close-admin-delete-modal]").forEach((button) => {
        button.addEventListener("click", () => {
            closeModal("adminDeleteModal");
            pendingAdminDeleteForm = null;
        });
    });

    document.querySelectorAll("[data-close-user-status-modal]").forEach((button) => {
        button.addEventListener("click", () => {
            closeModal("adminUserStatusModal");
            pendingUserStatusForm = null;
        });
    });

    document.addEventListener("submit", async (event) => {
        const form = event.target.closest(".admin-filter-form");

        if (!form || !document.getElementById("adminAjaxContent")) return;

        event.preventDefault();

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = `${form.getAttribute("action")}?${params.toString()}`;

        await loadAdminAjaxPage(url);
    });

    document.addEventListener("click", async (event) => {
        const resetLink = event.target.closest("#adminAjaxContent .btn-secondary[href]");
        const paginationLink = event.target.closest("#adminAjaxContent .catalog-pagination .pagination-link");

        if (!document.getElementById("adminAjaxContent")) return;

        if (paginationLink) {
            event.preventDefault();
            await loadAdminAjaxPage(paginationLink.href);
            return;
        }

        if (resetLink && resetLink.textContent.trim().toLowerCase().includes("réinitialiser")) {
            event.preventDefault();
            await loadAdminAjaxPage(resetLink.href);
        }
    });

    window.addEventListener("popstate", async () => {
        if (document.getElementById("adminAjaxContent")) {
            await loadAdminAjaxPage(window.location.href, false);
            if (window.GameStats.refreshNotificationsCount) {
                await window.GameStats.refreshNotificationsCount();
            }
        }
    });

    bindAdminConfirmations();
});