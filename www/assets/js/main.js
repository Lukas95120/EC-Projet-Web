document.addEventListener("DOMContentLoaded", () => {

    function showToast(message, type = "success") {
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
    }

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
                        if (message) message.textContent = "";
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

                    showToast(data.message, "success");

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

                    if (message) {
                        message.textContent = "";
                    }

                } catch (error) {
                    showToast("Erreur de connexion.", "error");
                    if (message) message.textContent = "";
                }

                button.disabled = false;
            });
        });
    }

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

            const cards = catalogContent.querySelectorAll(".catalog-card");

            cards.forEach((card, index) => {
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

        catalogFilterForm.addEventListener("submit", async (event) => {
            event.preventDefault();
            submitCatalogFilters(true);
        });

        catalogFilterForm.querySelectorAll("select").forEach((select) => {
            select.addEventListener("change", () => {
                submitCatalogFilters(false);
            });
        });

        const instantInputs = catalogFilterForm.querySelectorAll("#search, #year");

        instantInputs.forEach((input) => {
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

    function bindCatalogPagination(scope = document) {
        const paginationLinks = scope.querySelectorAll(".pagination-link");

        paginationLinks.forEach((link) => {
            if (link.dataset.bound === "true") return;

            link.dataset.bound = "true";

            link.addEventListener("click", async (event) => {
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

    window.addEventListener("popstate", async () => {
        syncCatalogFiltersWithUrl();
        await loadCatalog(window.location.href, false, false);
    });

    document.addEventListener("click", (event) => {
        const link = event.target.closest(".catalog-detail-link");

        if (!link) {
            return;
        }

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

    const reviewForm = document.getElementById("reviewForm");
    const reviewsList = document.getElementById("reviewsList");
    const reviewMessage = document.getElementById("reviewMessage");

    function updateAverageRating(averageRating, reviewCount) {
        const averageStars = document.getElementById("averageStars");
        const averageText = document.getElementById("averageText");
        const heroAverageRating = document.getElementById("heroAverageRating");
        const heroReviewCount = document.getElementById("heroReviewCount");

        if (averageStars && averageText) {
            if (!averageRating || reviewCount === 0) {
                averageStars.textContent = "";
                averageText.textContent = "Aucune note utilisateur pour le moment.";
            } else {
                const rounded = Math.round(averageRating);
                averageStars.textContent = "⭐".repeat(rounded) + "☆".repeat(5 - rounded);
                averageText.innerHTML = `<strong>${averageRating}/5</strong> sur ${reviewCount} avis`;
            }
        }

        if (heroAverageRating) {
            heroAverageRating.textContent = averageRating && reviewCount > 0
                ? `${averageRating}/5`
                : "—";
        }

        if (heroReviewCount) {
            heroReviewCount.textContent = reviewCount;
        }
    }

    function bindDeleteReviewForm(form) {
        form.addEventListener("submit", async (event) => {
            event.preventDefault();

            const button = form.querySelector("button");
            const formData = new FormData(form);
            const reviewCard = form.closest(".review-card");

            button.disabled = true;
            button.textContent = "Suppression...";

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
                    showToast(data.message || "Impossible de supprimer l’avis.", "error");
                    button.textContent = "Supprimer mon avis";
                    button.disabled = false;
                    return;
                }

                if (reviewCard) {
                    reviewCard.remove();
                }

                updateAverageRating(data.average_rating, data.review_count);
                showToast(data.message, "success");

                if (reviewMessage) {
                    reviewMessage.textContent = "";
                }

                if (data.review_count === 0 && reviewsList) {
                    reviewsList.innerHTML = `
                        <article class="card" id="noReviewsMessage">
                            <h3>Aucun avis pour le moment</h3>
                            <p>Sois le premier à laisser une note et un commentaire.</p>
                        </article>
                    `;
                }

            } catch (error) {
                showToast("Erreur de connexion.", "error");
                button.textContent = "Supprimer mon avis";
                button.disabled = false;
            }
        });
    }

    document.querySelectorAll(".delete-review-form").forEach((form) => {
        bindDeleteReviewForm(form);
    });

    if (reviewForm && reviewsList) {
        reviewForm.addEventListener("submit", async (event) => {
            event.preventDefault();

            const submitButton = reviewForm.querySelector("[data-review-submit]");
            const formData = new FormData(reviewForm);

            submitButton.disabled = true;

            if (reviewMessage) {
                reviewMessage.textContent = "Ajout de l’avis...";
            }

            try {
                const response = await fetch(reviewForm.action, {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    showToast(data.message || "Une erreur est survenue.", "error");
                    if (reviewMessage) reviewMessage.textContent = "";
                    submitButton.disabled = false;
                    return;
                }

                const noReviewsMessage = document.getElementById("noReviewsMessage");

                if (noReviewsMessage) {
                    noReviewsMessage.remove();
                }

                const review = data.review;
                const article = document.createElement("article");

                article.className = "card review-card";
                article.dataset.reviewId = review.id;

                article.innerHTML = `
                    <h3>${review.username}</h3>
                    <div class="review-stars">${review.stars}</div>
                    <p>Note : ${review.rating}/5</p>
                    ${review.comment ? `<p>${review.comment}</p>` : ""}
                    <form action="delete_review.php" method="POST" class="delete-review-form">
                        <input type="hidden" name="csrf_token" value="${reviewForm.querySelector("[name='csrf_token']").value}">
                        <input type="hidden" name="review_id" value="${review.id}">
                        <input type="hidden" name="game_id" value="${reviewForm.querySelector("[name='game_id']").value}">
                        <button class="btn btn-danger" type="submit">Supprimer mon avis</button>
                    </form>
                `;

                reviewsList.prepend(article);

                const deleteForm = article.querySelector(".delete-review-form");
                bindDeleteReviewForm(deleteForm);

                updateAverageRating(data.average_rating, data.review_count);
                reviewForm.reset();
                showToast(data.message, "success");

                if (reviewMessage) {
                    reviewMessage.textContent = "";
                }

            } catch (error) {
                showToast("Erreur de connexion.", "error");
                if (reviewMessage) reviewMessage.textContent = "";
            }

            submitButton.disabled = false;
        });
    }

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

    function bindCardMouseEffect(scope = document) {
        const cards = scope.querySelectorAll(".game-card, .card");

        cards.forEach((card) => {
            if (card.dataset.mouseBound === "true") return;

            card.dataset.mouseBound = "true";

            card.addEventListener("mousemove", (event) => {
                const rect = card.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                card.style.setProperty("--mouse-x", `${x}px`);
                card.style.setProperty("--mouse-y", `${y}px`);
            });
        });
    }

    bindCardMouseEffect();
});