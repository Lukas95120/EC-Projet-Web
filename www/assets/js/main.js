document.addEventListener("DOMContentLoaded", () => {

    // ===== TOAST NOTIFICATIONS =====
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

        setTimeout(() => {
            toast.classList.add("toast-hide");
        }, 2600);

        setTimeout(() => {
            toast.remove();
        }, 3100);
    }

    // ===== MENU MOBILE =====
    const menuToggle = document.getElementById("menuToggle");
    const mainNav = document.getElementById("mainNav");

    if (menuToggle && mainNav) {
        menuToggle.addEventListener("click", () => {
            mainNav.classList.toggle("open");
        });
    }

    // ===== ALERTES AUTO DISPARITION PREMIUM =====
    const alerts = document.querySelectorAll(".alert");

    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.classList.add("alert-hide");
        }, 3500);

        setTimeout(() => {
            alert.remove();
        }, 4300);
    });

    // ===== NOM FICHIER AVATAR =====
    const avatarInput = document.getElementById("avatar");
    const fileName = document.getElementById("fileName");

    if (avatarInput && fileName) {
        avatarInput.addEventListener("change", () => {
            fileName.textContent = avatarInput.files.length > 0
                ? avatarInput.files[0].name
                : "Aucun fichier choisi";
        });
    }

    // ===== FAVORIS SANS RECHARGEMENT =====
    const favoriteForms = document.querySelectorAll(".favorite-form");

    favoriteForms.forEach((form) => {
        form.addEventListener("submit", async (event) => {
            event.preventDefault();

            const button = form.querySelector("[data-favorite-button]");
            const message = form.querySelector("[data-favorite-message]");
            const formData = new FormData(form);

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

                button.textContent = data.button_text;

                if (data.is_favorite) {
                    button.classList.add("btn-danger");
                    form.action = form.dataset.removeAction;
                } else {
                    button.classList.remove("btn-danger");
                    form.action = form.dataset.addAction;
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

    // ===== AVIS SANS RECHARGEMENT =====
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

                    if (reviewMessage) {
                        reviewMessage.textContent = "";
                    }

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

                if (reviewMessage) {
                    reviewMessage.textContent = "";
                }
            }

            submitButton.disabled = false;
        });
    }

    // ===== EFFET SOURIS SUR CARTES =====
    const cards = document.querySelectorAll(".game-card, .card");

    cards.forEach((card) => {
        card.addEventListener("mousemove", (event) => {
            const rect = card.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            card.style.setProperty("--mouse-x", `${x}px`);
            card.style.setProperty("--mouse-y", `${y}px`);
        });
    });

});