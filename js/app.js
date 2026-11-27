(() => {
  "use strict";

  const page = document.querySelector(".page-shell")?.dataset.page || "";
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";

  const feedback = (element, text, isError = false) => {
    if (!element) return;
    element.textContent = text;
    element.classList.toggle("is-error", isError);
    element.hidden = false;
  };

  const mobileMenu = document.querySelector(".menu-toggle");
  mobileMenu?.addEventListener("click", () => {
    const nav = document.querySelector(".site-nav");
    const isOpen = mobileMenu.getAttribute("aria-expanded") === "true";
    mobileMenu.setAttribute("aria-expanded", String(!isOpen));
    mobileMenu.setAttribute("aria-label", isOpen ? "Menu openen" : "Menu sluiten");
    nav?.classList.toggle("is-open", !isOpen);
  });

  if (page === "create" || page === "edit") {
    const form = document.getElementById("game-form");
    const message = document.getElementById("game-form-feedback");
    const submit = document.getElementById("game-save");
    const deleteButton = document.getElementById("game-delete");
    const gameId = Number(form?.dataset.gameId || 0);
    const fields = form?.elements;
    let currentImage = form?.dataset.currentImage || "";

    form?.querySelector(".form-image-preview")?.addEventListener("error", () => {
      currentImage = "";
    });

    deleteButton?.addEventListener("click", async () => {
      if (!gameId || !window.confirm(`Weet je zeker dat je "${fields.title.value}" uit de gedeelde bibliotheek wilt verwijderen?`)) return;
      deleteButton.disabled = true;
      try {
        const response = await fetch("games-api.php", {
          method: "DELETE",
          headers: { "Content-Type": "application/json", "X-CSRF-Token": csrfToken },
          body: JSON.stringify({ id: gameId }),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || "Verwijderen is niet gelukt.");
        window.location.href = "05-overzicht-games.php";
      } catch (error) {
        feedback(message, error.message, true);
        deleteButton.disabled = false;
      }
    });

    form?.addEventListener("submit", async (event) => {
      event.preventDefault();
      message.hidden = true;
      if (!form.reportValidity()) return;

      const image = fields.cover_image.files[0];
      if (image && !["image/jpeg", "image/png", "image/webp", "image/gif"].includes(image.type)) {
        return feedback(message, "Kies een JPG-, PNG-, WebP- of GIF-afbeelding.", true);
      }
      if (image && image.size > 3 * 1024 * 1024) {
        return feedback(message, "De afbeelding mag maximaal 3 MB zijn.", true);
      }

      const game = {
        id: gameId || undefined,
        title: fields.title.value.trim(),
        platform: fields.platform.value,
        genre: fields.genre.value,
        release_year: Number(fields.release_year.value),
        status: fields.status.value,
        rating: fields.rating.value === "" ? null : Number(fields.rating.value),
        description: fields.description.value.trim(),
        image: currentImage || null,
      };

      submit.disabled = true;
      deleteButton.disabled = true;
      submit.textContent = image ? "Omslag uploaden..." : "Opslaan...";
      try {
        if (image) {
          const formData = new FormData();
          formData.append("cover_image", image);
          const uploadResponse = await fetch("upload-image.php", { method: "POST", body: formData });
          const uploadResult = await uploadResponse.json();
          if (!uploadResponse.ok) throw new Error(uploadResult.error || "De afbeelding kon niet worden geüpload.");
          game.image = uploadResult.path;
        }

        const response = await fetch("games-api.php", {
          method: gameId ? "PUT" : "POST",
          headers: { "Content-Type": "application/json", "X-CSRF-Token": csrfToken },
          body: JSON.stringify(game),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || "Opslaan is niet gelukt.");
        window.location.href = `09-detail-game.php?id=${encodeURIComponent(result.game.id)}`;
      } catch (error) {
        feedback(message, error.message, true);
        submit.disabled = false;
        deleteButton.disabled = false;
        submit.innerHTML = `${gameId ? "Wijzigingen opslaan" : "Game toevoegen"} <span>→</span>`;
      }
    });
  }

  document.querySelectorAll(".delete-game[data-delete-id]").forEach((button) => {
    button.addEventListener("click", async () => {
      const title = button.dataset.gameTitle || "deze game";
      if (!window.confirm(`Weet je zeker dat je "${title}" uit de gedeelde bibliotheek wilt verwijderen?`)) return;
      button.disabled = true;
      try {
        const response = await fetch("games-api.php", {
          method: "DELETE",
          headers: { "Content-Type": "application/json", "X-CSRF-Token": csrfToken },
          body: JSON.stringify({ id: Number(button.dataset.deleteId) }),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || "Verwijderen is niet gelukt.");
        window.location.href = "05-overzicht-games.php";
      } catch (error) {
        window.alert(error.message);
        button.disabled = false;
      }
    });
  });

  if (page === "contact") {
    const form = document.getElementById("contact-form");
    const message = document.getElementById("contact-feedback");
    form?.addEventListener("submit", (event) => {
      event.preventDefault();
      message.hidden = true;
      if (!form.reportValidity()) return;
      const details = new FormData(form);
      const subject = `[GameSpark] ${details.get("subject")}`;
      const body = `Naam: ${details.get("name")}\nE-mailadres: ${details.get("email")}\n\n${details.get("message")}`;
      feedback(message, "Je e-mailprogramma wordt geopend. Verstuur het bericht daar om het naar ons te verzenden.");
      window.location.href = `mailto:info@gamespark.nl?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    });
  }
})();
