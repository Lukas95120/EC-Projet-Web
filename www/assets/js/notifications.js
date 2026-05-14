document.addEventListener("DOMContentLoaded", () => {
    const showToast = window.GameStats.showToast;

    function updateNotificationsBadge(count) {
        const notificationsLink = document.querySelector('a[href$="notifications.php"]');
        let badge = document.getElementById("notificationsBadge");

        if (!notificationsLink) return;

        if (count > 0) {
            if (!badge) {
                badge = document.createElement("span");
                badge.className = "nav-badge";
                badge.id = "notificationsBadge";
                notificationsLink.appendChild(badge);
            }

            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }

    function getNotificationsCsrfToken() {
        const container = document.getElementById("notificationsGrid");
        return container ? container.dataset.csrfToken : "";
    }

    function getNotificationMeta(type) {
        const normalizedType = String(type || "").toLowerCase().trim();

        const types = {
            moderation: {
                label: "Modération",
                icon: "🛡️",
                className: "notification-type-moderation"
            },
            badge: {
                label: "Badge",
                icon: "🏆",
                className: "notification-type-badge"
            },
            ticket: {
                label: "Ticket",
                icon: "🎫",
                className: "notification-type-ticket"
            },
            friend: {
                label: "Ami",
                icon: "🤝",
                className: "notification-type-friend"
            },
            message: {
                label: "Message privé",
                icon: "💬",
                className: "notification-type-message"
            },
            system: {
                label: "Système",
                icon: "⚙️",
                className: "notification-type-system"
            }
        };

        return types[normalizedType] || types.system;
    }

    function bindNotificationForms() {
        document.querySelectorAll(".notification-read-form").forEach((form) => {
            if (form.dataset.bound === "true") return;

            form.dataset.bound = "true";

            form.addEventListener("submit", async (event) => {
                event.preventDefault();

                const card = form.closest(".notification-card");
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: "POST",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!data.success) {
                        showToast(data.message || "Erreur.", "error");
                        return;
                    }

                    const status = card.querySelector(".notification-status");

                    if (status) {
                        status.className = "status-badge status-added notification-status";
                        status.textContent = "Lue";
                    }

                    form.remove();
                    updateNotificationsBadge(data.unread_count);
                    showToast(data.message, "success");

                } catch (error) {
                    showToast("Erreur de connexion.", "error");
                }
            });
        });

        document.querySelectorAll(".notification-delete-form").forEach((form) => {
            if (form.dataset.bound === "true") return;

            form.dataset.bound = "true";

            form.addEventListener("submit", async (event) => {
                event.preventDefault();

                const card = form.closest(".notification-card");
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: "POST",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!data.success) {
                        showToast(data.message || "Erreur.", "error");
                        return;
                    }

                    if (card) {
                        card.remove();
                    }

                    updateNotificationsBadge(data.unread_count);
                    showToast(data.message, "success");

                    const remainingCards = document.querySelectorAll(".notification-card");
                    const notificationsContainer = document.getElementById("notificationsGrid");

                    if (remainingCards.length === 0 && notificationsContainer) {
                        notificationsContainer.innerHTML = `
                            <article class="info-box">
                                <h2>Aucune notification</h2>
                                <p>Tu n’as pas encore reçu de notification.</p>
                            </article>
                        `;
                    }

                } catch (error) {
                    showToast("Erreur de connexion.", "error");
                }
            });
        });
    }

    const readAllNotificationsForm = document.getElementById("readAllNotificationsForm");

    if (readAllNotificationsForm) {
        readAllNotificationsForm.addEventListener("submit", async (event) => {
            event.preventDefault();

            const formData = new FormData(readAllNotificationsForm);

            try {
                const response = await fetch(readAllNotificationsForm.action, {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    showToast(data.message || "Erreur.", "error");
                    return;
                }

                document.querySelectorAll(".notification-status").forEach((status) => {
                    status.className = "status-badge status-added notification-status";
                    status.textContent = "Lue";
                });

                document.querySelectorAll(".notification-read-form").forEach((form) => {
                    form.remove();
                });

                updateNotificationsBadge(0);
                showToast(data.message, "success");

            } catch (error) {
                showToast("Erreur de connexion.", "error");
            }
        });
    }

    async function refreshNotificationsCount() {
        const notificationsLink = document.querySelector('a[href$="notifications.php"]');

        if (!notificationsLink) return;

        try {
            const response = await fetch(`${window.GameStats.basePath || ""}notifications_count.php?t=${Date.now()}`, {
                cache: "no-store",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const data = await response.json();

            if (!data.success) return;

            updateNotificationsBadge(data.count);

        } catch (error) {
            console.error("Erreur lors du rafraîchissement des notifications :", error);
        }
    }

    let isDeletingAllNotifications = false;
    const notificationsContainer = document.getElementById("notificationsGrid");

    function createNotificationHtml(notification) {
        const csrfToken = getNotificationsCsrfToken();
        const meta = getNotificationMeta(notification.type);

        const statusBadge = Number(notification.is_read) === 0
            ? `<span class="status-badge status-pending notification-status">Non lue</span>`
            : `<span class="status-badge status-added notification-status">Lue</span>`;

        const readButton = Number(notification.is_read) === 0
            ? `
                <form action="notification_read.php" method="POST" class="notification-read-form">
                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                    <input type="hidden" name="notification_id" value="${notification.id}">
                    <button class="btn btn-secondary" type="submit">Marquer comme lu</button>
                </form>
            `
            : "";

        const viewButton = notification.link
            ? `<a href="${notification.link}" class="btn btn-secondary">Voir</a>`
            : "";

        return `
            <article class="info-box notification-card" data-notification-id="${notification.id}">
                <span class="notification-type-badge ${meta.className}">
                    ${meta.icon}
                    ${meta.label}
                </span>

                ${statusBadge}

                <h2>${notification.title}</h2>

                <p>${notification.message}</p>

                <p class="form-help">${notification.created_at}</p>

                <div class="notification-actions">
                    ${viewButton}

                    ${readButton}

                    <form action="notification_delete.php" method="POST" class="notification-delete-form">
                        <input type="hidden" name="csrf_token" value="${csrfToken}">
                        <input type="hidden" name="notification_id" value="${notification.id}">
                        <button class="btn btn-danger" type="submit">Supprimer</button>
                    </form>
                </div>
            </article>
        `;
    }

    async function refreshNotificationsPage() {
        if (!notificationsContainer || isDeletingAllNotifications) return;

        try {
            const response = await fetch(`notifications_fetch.php?t=${Date.now()}`, {
                cache: "no-store",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const data = await response.json();

            if (!data.success) return;

            if (data.notifications.length === 0) {
                notificationsContainer.innerHTML = `
                    <article class="info-box">
                        <h2>Aucune notification</h2>
                        <p>Tu n’as pas encore reçu de notification.</p>
                    </article>
                `;
                return;
            }

            notificationsContainer.innerHTML = data.notifications
                .map((notification) => createNotificationHtml(notification))
                .join("");

            bindNotificationForms();

        } catch (error) {
            console.error("Erreur lors du rafraîchissement des notifications :", error);
        }
    }

    window.GameStats.updateNotificationsBadge = updateNotificationsBadge;
    window.GameStats.refreshNotificationsCount = refreshNotificationsCount;
    window.GameStats.refreshNotificationsPage = refreshNotificationsPage;

    bindNotificationForms();

    refreshNotificationsCount();
    setInterval(refreshNotificationsCount, 3000);

    if (notificationsContainer) {
        refreshNotificationsPage();
        setInterval(refreshNotificationsPage, 3000);
    }

    const deleteAllNotificationsForm = document.getElementById("deleteAllNotificationsForm");

    if (deleteAllNotificationsForm) {
        deleteAllNotificationsForm.addEventListener("submit", async (event) => {
            event.preventDefault();

            const modal = document.getElementById("deleteNotificationsModal");
            const confirmButton = document.getElementById("confirmDeleteAllNotifications");

            if (!modal || !confirmButton) return;

            modal.classList.add("open");
            modal.setAttribute("aria-hidden", "false");

            confirmButton.onclick = async () => {
                isDeletingAllNotifications = true;

                modal.classList.remove("open");
                modal.setAttribute("aria-hidden", "true");

                const formData = new FormData(deleteAllNotificationsForm);

                try {
                    const response = await fetch(deleteAllNotificationsForm.action, {
                        method: "POST",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!data.success) {
                        showToast(data.message || "Erreur.", "error");
                        isDeletingAllNotifications = false;
                        return;
                    }

                    if (notificationsContainer) {
                        notificationsContainer.innerHTML = `
                            <article class="info-box">
                                <h2>Aucune notification</h2>
                                <p>Tu n’as pas encore reçu de notification.</p>
                            </article>
                        `;
                    }

                    updateNotificationsBadge(0);
                    showToast(data.message, "success");
                    isDeletingAllNotifications = false;

                } catch (error) {
                    isDeletingAllNotifications = false;
                    showToast("Erreur de connexion.", "error");
                }
            };
        });
    }

    document.querySelectorAll("[data-close-modal]").forEach((button) => {
        button.addEventListener("click", () => {
            const modal = document.getElementById("deleteNotificationsModal");

            if (modal) {
                modal.classList.remove("open");
                modal.setAttribute("aria-hidden", "true");
            }
        });
    });
});