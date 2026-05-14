document.addEventListener("DOMContentLoaded", () => {
    const showToast = window.GameStats.showToast;
    const clearInappropriateFields = window.GameStats.clearInappropriateFields;
    const bindCardMouseEffect = window.GameStats.bindCardMouseEffect;

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

    function getCurrentUserId() {
        const metaUser = document.querySelector("meta[name='current-user-id']");
        return metaUser ? metaUser.content : null;
    }

    function createReviewHtml(review, currentUserId = null) {
        const avatarHtml = review.avatar
            ? `<img src="assets/uploads/${review.avatar.split("/").pop()}" alt="${review.username}">`
            : review.username.charAt(0).toUpperCase();

        const csrfInput = reviewForm
            ? reviewForm.querySelector("[name='csrf_token']")
            : null;

        const gameIdInput = reviewForm
            ? reviewForm.querySelector("[name='game_id']")
            : null;

        const canDelete = currentUserId && Number(currentUserId) === Number(review.user_id);

        const deleteFormHtml = canDelete && csrfInput && gameIdInput
            ? `
                <form action="delete_review.php" method="POST" class="delete-review-form">
                    <input type="hidden" name="csrf_token" value="${csrfInput.value}">
                    <input type="hidden" name="review_id" value="${review.id}">
                    <input type="hidden" name="game_id" value="${gameIdInput.value}">
                    <button class="btn btn-danger" type="submit">Supprimer mon avis</button>
                </form>
            `
            : "";

        return `
            <article class="card review-card" data-review-id="${review.id}">
                <div class="review-user-header">
                    <a href="user.php?id=${review.user_id}" class="mini-user-link">
                        <span class="mini-user-avatar">
                            ${avatarHtml}
                        </span>

                        <strong>${review.username}</strong>
                    </a>
                </div>

                <div class="review-stars">${review.stars}</div>
                <p>Note : ${review.rating}/5</p>
                ${review.comment ? `<p>${review.comment}</p>` : ""}
                ${deleteFormHtml}
            </article>
        `;
    }

    function bindDeleteReviewForm(form) {
        if (!form || form.dataset.bound === "true") return;

        form.dataset.bound = "true";

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

    async function refreshReviews() {
        if (!reviewsList) return;

        const gameIdInput = reviewForm
            ? reviewForm.querySelector("[name='game_id']")
            : document.querySelector("[name='game_id']");

        if (!gameIdInput) return;

        try {
            const response = await fetch(`reviews_fetch.php?game_id=${gameIdInput.value}&t=${Date.now()}`, {
                cache: "no-store",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const data = await response.json();

            if (!data.success) return;

            updateAverageRating(data.average_rating, data.review_count);

            if (data.reviews.length === 0) {
                reviewsList.innerHTML = `
                    <article class="card" id="noReviewsMessage">
                        <h3>Aucun avis pour le moment</h3>
                        <p>Sois le premier à laisser une note et un commentaire.</p>
                    </article>
                `;
                return;
            }

            const currentUserId = getCurrentUserId();

            reviewsList.innerHTML = data.reviews
                .map((review) => createReviewHtml(review, currentUserId))
                .join("");

            document.querySelectorAll(".delete-review-form").forEach((form) => {
                bindDeleteReviewForm(form);
            });

            bindCardMouseEffect(reviewsList);

        } catch (error) {
            console.error("Erreur lors du rafraîchissement des avis :", error);
        }
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
                const errorMessage = data.message || "Une erreur est survenue.";

                if (!data.success) {
                    if (data.clear_content || errorMessage.toLowerCase().includes("inapproprié")) {
                        clearInappropriateFields(reviewForm);
                    }

                    showToast(errorMessage, "error");

                    if (reviewMessage) {
                        reviewMessage.textContent = "";
                    }

                    submitButton.disabled = false;
                    return;
                }

                reviewForm.reset();
                await refreshReviews();

                showToast(data.message, "success");

                if (reviewMessage) {
                    reviewMessage.textContent = "";
                }

            } catch (error) {
                showToast("Erreur de connexion.", "error");

                if (reviewMessage) {
                    reviewMessage.textContent = "";
                }
            }

            submitButton.disabled = false;
        });

        refreshReviews();
        setInterval(refreshReviews, 5000);
    }
});