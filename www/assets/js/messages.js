document.addEventListener("DOMContentLoaded", () => {

    const showToast = window.GameStats.showToast;

    const privateMessageForm = document.getElementById("privateMessageForm");
    const privateMessagesList = document.getElementById("privateMessagesList");
    const privateMessageInput = document.getElementById("privateMessageInput");

    const hasPrivateConversation =
        privateMessageForm &&
        privateMessagesList &&
        privateMessageInput;

    async function refreshMessagesCount() {

        const messagesLink = document.querySelector('a[href$="messages.php"]');

        if (!messagesLink) {
            return;
        }

        try {

            const response = await fetch(
                `${window.GameStats.basePath || ""}messages_count.php?t=${Date.now()}`,
                {
                    cache: "no-store",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                }
            );

            const data = await response.json();

            if (!data.success) {
                return;
            }

            let badge = document.getElementById("messagesBadge");

            if (data.count > 0) {

                if (!badge) {

                    badge = document.createElement("span");
                    badge.className = "nav-badge";
                    badge.id = "messagesBadge";

                    messagesLink.appendChild(badge);
                }

                badge.textContent = data.count;

            } else if (badge) {

                badge.remove();
            }

        } catch (error) {

            console.error(
                "Erreur lors du rafraîchissement des messages :",
                error
            );
        }
    }

    window.GameStats.refreshMessagesCount = refreshMessagesCount;

    refreshMessagesCount();
    setInterval(refreshMessagesCount, 3000);

    if (!hasPrivateConversation) {
        return;
    }

    privateMessageForm.addEventListener("submit", async (event) => {

        event.preventDefault();

        const button = privateMessageForm.querySelector("button");
        const formData = new FormData(privateMessageForm);

        button.disabled = true;
        button.textContent = "Envoi...";

        try {

            const response = await fetch(privateMessageForm.action, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });

            const data = await response.json();

            if (!data.success) {

                showToast(data.message || "Erreur.", "error");

                button.disabled = false;
                button.textContent = "Envoyer";

                return;
            }

            privateMessageInput.value = "";

            await refreshPrivateMessages();

            await refreshMessagesCount();

        } catch (error) {

            showToast("Erreur de connexion.", "error");
        }

        button.disabled = false;
        button.textContent = "Envoyer";
    });

    async function refreshPrivateMessages() {

        const conversationId =
            privateMessagesList.dataset.conversationId;

        if (!conversationId) {
            return;
        }

        try {

            const response = await fetch(
                `private_messages_fetch.php?conversation_id=${conversationId}&t=${Date.now()}`,
                {
                    cache: "no-store",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                }
            );

            const data = await response.json();

            if (!data.success) {
                return;
            }

            if (data.messages.length === 0) {

                privateMessagesList.innerHTML = `
                    <article class="info-box">
                        <h2>Aucun message</h2>
                        <p>Commence la discussion.</p>
                    </article>
                `;

                return;
            }

            privateMessagesList.innerHTML = data.messages
                .map((message) => createPrivateMessageHtml(message))
                .join("");

            await refreshMessagesCount();

        } catch (error) {

            console.error(
                "Erreur lors du rafraîchissement des messages privés :",
                error
            );
        }
    }

    function createPrivateMessageHtml(message) {

        const avatarHtml = message.avatar
            ? `<img src="assets/uploads/${message.avatar.split("/").pop()}" alt="${message.username}">`
            : message.username.charAt(0).toUpperCase();

        const mineClass = Number(message.is_mine) === 1
            ? "private-message-mine"
            : "private-message-other";

        return `
            <article class="private-message ${mineClass}">

                <div class="private-message-header">

                    <span class="mini-user-avatar">
                        ${avatarHtml}
                    </span>

                    <strong>${message.username}</strong>

                    <span>${message.created_at}</span>

                </div>

                <p>${message.message}</p>

            </article>
        `;
    }

    refreshPrivateMessages();
    setInterval(refreshPrivateMessages, 3000);

});