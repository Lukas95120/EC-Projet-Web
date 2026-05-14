document.addEventListener("DOMContentLoaded", () => {
    const showToast = window.GameStats.showToast;
    const bindCardMouseEffect = window.GameStats.bindCardMouseEffect;

    function bindFavoriteForms(scope = document) {
        const favoriteForms = scope.querySelectorAll(".favorite-form");

        favoriteForms.forEach((form) => {
            if (form.dataset.bound === "true") return;

            form.dataset.bound = "true";

            form.addEventListener("submit", async (event) => {
                event.preventDefault();

                const button = form.querySelector("[data-favorite-button]");
                const message = form.querySelector("[data-favorite-message]");
                const formData = new FormData(form);

                if (!button) return;

                const isCatalogButton = form.closest(".catalog-card") !== null;

                button.disabled = true;

                if (message) {
                    message.textContent = "Mise à jour...";
                }

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
                        showToast(data.message || "Une erreur est survenue.", "error");

                        if (message) {
                            message.textContent = "";
                        }

                        button.disabled = false;
                        return;
                    }

                    button.textContent = isCatalogButton
                        ? (data.is_favorite ? "Retirer" : "Favori")
                        : data.button_text;

                    if (data.is_favorite) {
                        button.classList.add("btn-danger");
                        form.action = form.dataset.removeAction;
                    } else {
                        button.classList.remove("btn-danger");
                        form.action = form.dataset.addAction;
                    }

                    const catalogCard = form.closest(".catalog-card");
                    const imageWrap = catalogCard ? catalogCard.querySelector(".catalog-image-wrap") : null;
                    const existingBadge = catalogCard ? catalogCard.querySelector(".catalog-favorite-badge") : null;

                    if (catalogCard && imageWrap) {
                        if (data.is_favorite && !existingBadge) {
                            const badge = document.createElement("span");
                            badge.className = "favorite-badge catalog-favorite-badge";
                            badge.textContent = "❤️ Favori";
                            imageWrap.appendChild(badge);
                        }

                        if (!data.is_favorite && existingBadge) {
                            existingBadge.remove();
                        }
                    }

                    const favoriteCard = form.closest(".favorite-card");

                    if (favoriteCard && !data.is_favorite) {
                        favoriteCard.remove();

                        const remainingFavorites = document.querySelectorAll(".favorite-card");

                        if (remainingFavorites.length === 0) {
                            const gamesGrid = document.querySelector(".games-grid");

                            if (gamesGrid) {
                                gamesGrid.outerHTML = `
                                    <div class="info-box">
                                        <h2>Aucun favori</h2>
                                        <p>Tu n’as pas encore ajouté de jeux à tes favoris.</p>
                                        <a href="games.php" class="btn">Explorer les jeux</a>
                                    </div>
                                `;
                            }
                        }
                    }

                    showToast(data.message, "success");

                    if (message) {
                        message.textContent = "";
                    }

                } catch (error) {
                    showToast("Erreur de connexion.", "error");

                    if (message) {
                        message.textContent = "";
                    }
                }

                button.disabled = false;
            });
        });
    }

    window.GameStats.bindFavoriteForms = bindFavoriteForms;

    bindFavoriteForms();

    const catalogFilterForm = document.getElementById("catalogFilterForm");
    const catalogContent = document.getElementById("catalogContent");
    const catalogResetButton = document.getElementById("catalogResetButton");

    function syncCatalogFiltersWithUrl() {
        if (!catalogFilterForm) return;

        const params = new URLSearchParams(window.location.search);
        const fields = ["search", "genre", "platform", "year", "sort"];

        fields.forEach((field) => {
            const input = catalogFilterForm.querySelector(`[name="${field}"]`);

            if (input) {
                input.value = params.get(field) || (field === "sort" ? "title_asc" : "");
            }
        });
    }

    async function loadCatalog(url, showSuccessToast = false, updateHistory = true) {
        if (!catalogContent) {
            window.location.href = url;
            return;
        }

        catalogContent.classList.add("catalog-loading");

        catalogContent.innerHTML = `
            <div class="catalog-skeleton-grid">
                ${Array.from({ length: 6 }).map(() => `
                    <article class="catalog-skeleton-card">
                        <div class="catalog-skeleton-image"></div>
                        <div class="catalog-skeleton-line large"></div>
                        <div class="catalog-skeleton-line"></div>
                        <div class="catalog-skeleton-line short"></div>
                    </article>
                `).join("")}
            </div>
        `;

        try {
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newContent = doc.getElementById("catalogContent");

            if (!newContent) {
                throw new Error("Contenu catalogue introuvable.");
            }

            catalogContent.innerHTML = newContent.innerHTML;

            if (updateHistory) {
                window.history.pushState({}, "", url);
                syncCatalogFiltersWithUrl();
            }

            bindFavoriteForms(catalogContent);
            bindCatalogPagination(catalogContent);
            bindCardMouseEffect(catalogContent);

            catalogContent.querySelectorAll(".catalog-card").forEach((card, index) => {
                card.style.animationDelay = `${index * 0.05}s`;
            });

            if (showSuccessToast) {
                showToast("Catalogue mis à jour.", "success");
            }

        } catch (error) {
            showToast("Erreur pendant la recherche.", "error");
        }

        catalogContent.classList.remove("catalog-loading");
    }

    function bindCatalogPagination(scope = document) {
        const paginationLinks = scope.querySelectorAll(".pagination-link");

        paginationLinks.forEach((link) => {
            if (link.dataset.bound === "true") return;

            link.dataset.bound = "true";

            link.addEventListener("click", async (event) => {
                if (!catalogContent) return;

                event.preventDefault();

                await loadCatalog(link.href, false, true);

                const catalogTop = document.querySelector(".catalog-results");

                if (catalogTop) {
                    catalogTop.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                }
            });
        });
    }

    bindCatalogPagination();

    if (catalogFilterForm && catalogContent) {
        let searchTimeout;

        syncCatalogFiltersWithUrl();

        function submitCatalogFilters(showToastAfterSearch = false) {
            const formData = new FormData(catalogFilterForm);
            formData.delete("page");

            const params = new URLSearchParams(formData);
            const queryString = params.toString();
            const url = queryString ? `games.php?${queryString}` : "games.php";

            loadCatalog(url, showToastAfterSearch, true);
        }

        catalogFilterForm.addEventListener("submit", (event) => {
            event.preventDefault();
            submitCatalogFilters(true);
        });

        catalogFilterForm.querySelectorAll("select").forEach((select) => {
            select.addEventListener("change", () => {
                submitCatalogFilters(false);
            });
        });

        catalogFilterForm.querySelectorAll("#search, #year").forEach((input) => {
            input.addEventListener("input", () => {
                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(() => {
                    submitCatalogFilters(false);
                }, 450);
            });
        });
    }

    if (catalogResetButton) {
        catalogResetButton.addEventListener("click", async (event) => {
            event.preventDefault();

            if (catalogFilterForm) {
                catalogFilterForm.reset();
            }

            await loadCatalog("games.php", true, true);
        });
    }

    window.addEventListener("popstate", async () => {
        if (!catalogContent) return;

        syncCatalogFiltersWithUrl();
        await loadCatalog(window.location.href, false, false);
    });

    document.addEventListener("click", (event) => {
        const link = event.target.closest(".catalog-detail-link");

        if (!link) return;

        sessionStorage.setItem("catalogReturnUrl", window.location.href);
        sessionStorage.setItem("shouldRestoreCatalog", "true");
    });

    window.addEventListener("pageshow", async () => {
        const shouldRestore = sessionStorage.getItem("shouldRestoreCatalog");
        const savedUrl = sessionStorage.getItem("catalogReturnUrl");

        if (!catalogFilterForm || !catalogContent || shouldRestore !== "true" || !savedUrl) {
            return;
        }

        sessionStorage.removeItem("shouldRestoreCatalog");

        window.history.replaceState({}, "", savedUrl);
        syncCatalogFiltersWithUrl();

        await loadCatalog(savedUrl, false, false);
    });

    const favoriteSearch = document.getElementById("favoriteSearch");
    const favoritesGrid = document.getElementById("favoritesGrid");
    const favoritesEmptySearch = document.getElementById("favoritesEmptySearch");

    if (favoriteSearch && favoritesGrid) {
        favoriteSearch.addEventListener("input", () => {
            const search = favoriteSearch.value.toLowerCase().trim();
            const cards = favoritesGrid.querySelectorAll(".favorite-card");
            let visibleCount = 0;

            cards.forEach((card) => {
                const title = card.dataset.title || "";
                const genre = card.dataset.genre || "";
                const platform = card.dataset.platform || "";

                const match =
                    title.includes(search) ||
                    genre.includes(search) ||
                    platform.includes(search);

                if (match) {
                    card.classList.remove("hidden-favorite");
                    visibleCount++;
                } else {
                    card.classList.add("hidden-favorite");
                }
            });

            if (favoritesEmptySearch) {
                favoritesEmptySearch.style.display = visibleCount === 0 ? "block" : "none";
            }
        });
    }
});