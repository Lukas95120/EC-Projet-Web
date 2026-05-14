document.addEventListener("DOMContentLoaded", () => {
    const bindCardMouseEffect = window.GameStats.bindCardMouseEffect;
    const forumMessagesList = document.getElementById("forumMessagesList");

    function createForumMessageHtml(message) {
        const avatarHtml = message.avatar
            ? `<img src="assets/uploads/${message.avatar.split("/").pop()}" alt="${message.username}">`
            : message.username.charAt(0).toUpperCase();

        return `
            <article class="forum-message" data-message-id="${message.id}">
                <div class="forum-message-header forum-user-header">
                    <a href="user.php?id=${message.user_id}" class="mini-user-link">
                        <span class="mini-user-avatar">
                            ${avatarHtml}
                        </span>

                        <strong>${message.username}</strong>
                    </a>

                    <span>${message.created_at}</span>
                </div>

                <p>${message.message}</p>
            </article>
        `;
    }

    async function refreshForumMessages() {
        if (!forumMessagesList) return;

        try {
            const response = await fetch(`forum_fetch.php?t=${Date.now()}`, {
                cache: "no-store",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const data = await response.json();

            if (!data.success) return;

            if (data.messages.length === 0) {
                forumMessagesList.innerHTML = `
                    <div class="info-box">
                        <h2>Aucun message</h2>
                        <p>Sois le premier à lancer la discussion.</p>
                    </div>
                `;
                return;
            }

            forumMessagesList.innerHTML = data.messages
                .map((message) => createForumMessageHtml(message))
                .join("");

            bindCardMouseEffect(forumMessagesList);

        } catch (error) {
            console.error("Erreur lors du rafraîchissement du forum :", error);
        }
    }

    if (forumMessagesList) {
        refreshForumMessages();
        setInterval(refreshForumMessages, 5000);
    }
});