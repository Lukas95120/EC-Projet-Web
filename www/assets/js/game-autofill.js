document.addEventListener("DOMContentLoaded", () => {
    const autoFillButton = document.getElementById("autoFillGame");
    const messageBox = document.getElementById("autoFillMessage");

    if (!autoFillButton || !messageBox) {
        return;
    }

    autoFillButton.addEventListener("click", async () => {
        const titleInput = document.getElementById("title");
        const title = titleInput.value.trim();

        if (title === "") {
            messageBox.textContent = "Renseigne d’abord un titre.";
            return;
        }

        messageBox.textContent = "Recherche en cours...";
        autoFillButton.disabled = true;

        try {
            const response = await fetch("api_game_search.php?title=" + encodeURIComponent(title));
            const data = await response.json();

            if (!data.success) {
                messageBox.textContent = data.message || "Aucun résultat trouvé.";
                autoFillButton.disabled = false;
                return;
            }

            const game = data.game;

            if (game.title) {
                document.getElementById("title").value = game.title;
            }

            if (game.platform) {
                document.getElementById("platform").value = game.platform;
            }

            if (game.genre) {
                document.getElementById("genre").value = game.genre;
            }

            if (game.release_year) {
                document.getElementById("release_year").value = game.release_year;
            }

            if (game.publisher) {
                document.getElementById("publisher").value = game.publisher;
            }

            if (game.critic_score) {
                document.getElementById("critic_score").value = game.critic_score;
            }

            if (game.user_score) {
                document.getElementById("user_score").value = game.user_score;
            }

            if (game.image_url) {
                const imageInput = document.getElementById("image_url");
                const preview = document.getElementById("gameImagePreview");

                imageInput.value = game.image_url;

                preview.innerHTML = `
                    <img src="${game.image_url}" alt="Aperçu du jeu">
                `;
            }

            messageBox.textContent = "Informations trouvées et ajoutées au formulaire.";
        } catch (error) {
            messageBox.textContent = "Erreur pendant la recherche automatique.";
        }

        autoFillButton.disabled = false;
    });
});