document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("communitySearch");
    const resultsContainer = document.getElementById("communityResults");
    const statusText = document.getElementById("communitySearchStatus");

    if (!searchInput || !resultsContainer) {
        return;
    }

    let searchTimeout;

    async function loadCommunityMembers() {
        const search = searchInput.value.trim();

        if (statusText) {
            statusText.textContent = search
                ? "Recherche en cours..."
                : "Affichage des membres récents.";
        }

        try {
            const response = await fetch(
                `community_search.php?search=${encodeURIComponent(search)}&t=${Date.now()}`,
                {
                    cache: "no-store",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                }
            );

            const html = await response.text();

            resultsContainer.innerHTML = html;

            if (statusText) {
                statusText.textContent = search
                    ? "Résultats de recherche affichés."
                    : "Membres récents affichés.";
            }

        } catch (error) {
            resultsContainer.innerHTML = `
                <article class="info-box">
                    <h2>Erreur</h2>
                    <p>Impossible de charger les membres.</p>
                </article>
            `;

            if (statusText) {
                statusText.textContent = "Erreur pendant la recherche.";
            }
        }
    }

    searchInput.addEventListener("input", () => {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {
            loadCommunityMembers();
        }, 350);
    });

    loadCommunityMembers();
});