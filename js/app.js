(() => {
  "use strict";

  const collectionKey = "gamespark-games";
  const profileKey = "gamespark-profile";
  const catalog = Array.isArray(window.gameSparkCatalog) ? window.gameSparkCatalog : [];
  const page = document.querySelector(".page-shell")?.dataset.page || "";

  const escapeHtml = (value) => String(value ?? "").replace(/[&<>"']/g, (character) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  })[character]);

  const readCollection = () => {
    try {
      const saved = JSON.parse(localStorage.getItem(collectionKey) || "[]");
      if (!Array.isArray(saved)) throw new Error("De opgeslagen collectie heeft een ongeldig formaat.");
      let migrated = false;
      const games = saved.filter((game) => game && typeof game === "object").map((game) => {
        if (game.rating_scale === 10) return game;
        const oldRating = Number(game.rating);
        migrated = true;
        return {
          ...game,
          rating: oldRating > 0 && oldRating <= 5 ? oldRating * 2 : game.rating,
          rating_scale: 10,
        };
      });
      if (migrated) localStorage.setItem(collectionKey, JSON.stringify(games));
      return games;
    } catch (error) {
      console.error("GameSpark collectie kon niet worden gelezen.", error);
      showStorageError();
      return [];
    }
  };

  const writeCollection = (games) => {
    try {
      localStorage.setItem(collectionKey, JSON.stringify(games));
      return true;
    } catch (error) {
      console.error("GameSpark collectie kon niet worden opgeslagen.", error);
      return false;
    }
  };

  const feedback = (element, text, isError = false) => {
    if (!element) return;
    element.textContent = text;
    element.classList.toggle("is-error", isError);
    element.hidden = false;
  };

  const showStorageError = () => {
    let notice = document.querySelector("[data-storage-error]");
    if (!notice) {
      notice = document.createElement("p");
      notice.className = "form-feedback is-error";
      notice.setAttribute("role", "alert");
      notice.dataset.storageError = "";
      document.querySelector(".page-shell")?.prepend(notice);
    }
    feedback(notice, "Je collectie kon niet uit browseropslag worden gelezen. Controleer de opslaginstellingen van je browser.");
  };

  const statusLabel = (status) => ({
    Wishlist: "Wil ik spelen",
    "Speelt nu": "Speel ik nu",
  }[status] || status || "Geen status");

  const artTone = (game) => {
    const knownGame = catalog.find((item) => String(item.id) === String(game.id));
    return knownGame?.tone || "blue";
  };

  const mobileMenu = document.querySelector(".menu-toggle");
  mobileMenu?.addEventListener("click", () => {
    const nav = document.querySelector(".site-nav");
    const isOpen = mobileMenu.getAttribute("aria-expanded") === "true";
    mobileMenu.setAttribute("aria-expanded", String(!isOpen));
    mobileMenu.setAttribute("aria-label", isOpen ? "Menu openen" : "Menu sluiten");
    nav?.classList.toggle("is-open", !isOpen);
  });

  document.querySelectorAll(".game-card").forEach((card, index) => {
    card.style.setProperty("--card-order", String(index));
  });

  if (page === "games") {
    const grid = document.getElementById("catalog-grid");
    const genreButtons = [...document.querySelectorAll(".genre-filter")];
    const sortSelect = document.getElementById("catalog-sort");
    const emptyMessage = document.getElementById("catalog-empty");
    let activeGenre = "Alles";

    const sortCards = () => {
      if (!grid) return;
      const cards = [...grid.querySelectorAll(".game-card")];
      cards.sort((left, right) => {
        const a = catalog.find((game) => game.id === left.querySelector(".game-cover")?.getAttribute("href")?.split("id=")[1]);
        const b = catalog.find((game) => game.id === right.querySelector(".game-cover")?.getAttribute("href")?.split("id=")[1]);
        if (!a || !b) return 0;
        if (sortSelect?.value === "title") return a.title.localeCompare(b.title, "nl");
        if (sortSelect?.value === "year") return b.release_year - a.release_year;
        if (sortSelect?.value === "rating") return b.rating - a.rating;
        return catalog.indexOf(a) - catalog.indexOf(b);
      });
      cards.forEach((card) => grid.append(card));
    };

    genreButtons.forEach((button) => {
      button.addEventListener("click", () => {
        activeGenre = button.dataset.genre || "Alles";
        genreButtons.forEach((item) => item.classList.toggle("is-selected", item === button));
        let visibleCount = 0;
        grid?.querySelectorAll(".game-card").forEach((card) => {
          const title = card.querySelector(".game-name-line > a")?.textContent;
          const game = catalog.find((item) => item.title === title);
          const visible = activeGenre === "Alles" || game?.genre === activeGenre;
          card.hidden = !visible;
          if (visible) visibleCount++;
        });
        if (emptyMessage) emptyMessage.hidden = visibleCount > 0;
      });
    });
    sortSelect?.addEventListener("change", sortCards);
  }

  if (page === "collection") {
    const grid = document.getElementById("my-games");
    const count = document.getElementById("collection-count");
    const empty = document.getElementById("collection-empty");
    const search = document.getElementById("collection-search");
    const platform = document.getElementById("collection-platform");
    const status = document.getElementById("collection-status");
    const sort = document.getElementById("collection-sort");
    let games = readCollection();

    const updatePlatformOptions = () => {
      if (!platform) return;
      const selected = platform.value;
      const options = [...new Set(games.map((game) => game.platform).filter(Boolean))].sort((a, b) => a.localeCompare(b, "nl"));
      platform.innerHTML = '<option value="">Alle platforms</option>' +
        options.map((value) => `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`).join("");
      platform.value = options.includes(selected) ? selected : "";
    };

    const render = () => {
      if (!grid || !count || !empty) return;
      const query = search?.value.trim().toLocaleLowerCase("nl") || "";
      const visibleGames = games.filter((game) =>
        (!query || `${game.title} ${game.genre} ${game.platform}`.toLocaleLowerCase("nl").includes(query)) &&
        (!platform?.value || game.platform === platform.value) &&
        (!status?.value || game.status === status.value)
      );
      const key = sort?.value || "title";
      visibleGames.sort((a, b) => key === "title"
        ? String(a.title).localeCompare(String(b.title), "nl")
        : Number(b[key] || 0) - Number(a[key] || 0));
      count.textContent = String(games.length);
      empty.hidden = games.length > 0;
      grid.innerHTML = visibleGames.map((game) => {
        const tone = artTone(game);
        const cover = game.image_data
          ? `<img src="${escapeHtml(game.image_data)}" alt="">`
          : "";
        const rating = Number(game.rating) > 0 ? `<span class="score">★ ${escapeHtml(game.rating)}<small>/10</small></span>` : "";
        const detailsUrl = catalog.some((item) => String(item.id) === String(game.id))
          ? `09-detail-game.php?id=${encodeURIComponent(game.id)}`
          : `09-detail-game.php?id=${encodeURIComponent(game.id)}`;
        return `<article class="collection-card">
          <div class="collection-card-art cover-${escapeHtml(tone)}">${cover}<h2>${escapeHtml(game.title)}</h2></div>
          <div class="collection-card-body">
            <div class="collection-meta"><span>${escapeHtml(game.platform)} · ${escapeHtml(game.release_year)}</span><span class="status-pill ${game.status === "Speelt nu" ? "status-playing" : game.status === "Uitgespeeld" ? "status-done" : ""}">${escapeHtml(statusLabel(game.status))}</span></div>
            <p>${escapeHtml(game.genre)} ${rating ? ` · ${rating}` : ""}</p>
            ${game.notes ? `<p>${escapeHtml(game.notes)}</p>` : ""}
            <div class="collection-card-actions"><a href="${detailsUrl}">Details</a><a href="07-updaten.php?id=${encodeURIComponent(game.id)}">Bewerken</a><button type="button" class="delete-game" data-delete-id="${escapeHtml(game.id)}">Verwijderen</button></div>
          </div>
        </article>`;
      }).join("");
      const hasVisible = visibleGames.length > 0;
      if (games.length > 0 && !hasVisible) {
        empty.hidden = false;
        empty.querySelector("h2").textContent = "Geen games gevonden.";
        empty.querySelector("p").textContent = "Pas je zoekopdracht of filters aan.";
        empty.querySelector(".button").hidden = true;
      } else {
        empty.querySelector("h2").textContent = "Je collectie begint hier.";
        empty.querySelector("p").textContent = "Voeg je eerste game toe en maak deze plek helemaal van jou.";
        empty.querySelector(".button").hidden = false;
      }
    };

    search?.addEventListener("input", render);
    [platform, status, sort].forEach((control) => control?.addEventListener("change", render));
    grid?.addEventListener("click", (event) => {
      const button = event.target.closest("[data-delete-id]");
      if (!button) return;
      const target = games.find((game) => String(game.id) === button.dataset.deleteId);
      if (!target || !window.confirm(`Weet je zeker dat je "${target.title}" wilt verwijderen?`)) return;
      const updated = games.filter((game) => String(game.id) !== String(target.id));
      if (!writeCollection(updated)) {
        window.alert("Verwijderen is niet gelukt. Controleer de beschikbare browseropslag.");
        return;
      }
      games = updated;
      updatePlatformOptions();
      render();
    });
    updatePlatformOptions();
    render();
  }

  if (page === "create" || page === "edit") {
    const form = document.getElementById("game-form");
    const message = document.getElementById("game-form-feedback");
    const submit = document.getElementById("game-save");
    const deleteButton = document.getElementById("game-delete");
    const fields = form?.elements;
    const idFromUrl = new URLSearchParams(window.location.search).get("id");
    const initialGames = readCollection();
    const editingGame = page === "edit"
      ? initialGames.find((game) => String(game.id) === String(idFromUrl))
      : null;

    if (form && fields) {
      if (page === "edit" && !editingGame) {
        feedback(message, "Deze game staat niet in je collectie. Open de bewerkpagina vanuit je collectie.", true);
        submit.disabled = true;
      } else if (editingGame) {
        document.getElementById("game-form-heading").textContent = "Game bewerken.";
        submit.innerHTML = "Wijzigingen opslaan <span>→</span>";
        deleteButton.hidden = false;
        for (const field of ["title", "platform", "genre", "release_year", "status", "rating", "notes"]) {
          fields[field].value = editingGame[field] ?? "";
        }
        fields.id.value = editingGame.id;
      }

      const removeEditingGame = () => {
        if (!editingGame || !window.confirm(`Weet je zeker dat je "${editingGame.title}" wilt verwijderen?`)) return;
        const updated = readCollection().filter((game) => String(game.id) !== String(editingGame.id));
        if (!writeCollection(updated)) {
          feedback(message, "Verwijderen is niet gelukt. Controleer de beschikbare browseropslag.", true);
          return;
        }
        window.location.href = "05-overzicht-games.php";
      };
      deleteButton?.addEventListener("click", removeEditingGame);

      form.addEventListener("submit", async (event) => {
        event.preventDefault();
        message.hidden = true;
        if (!form.reportValidity()) return;
        const title = fields.title.value.trim();
        const year = Number(fields.release_year.value);
        const ratingValue = fields.rating.value;
        const image = fields.cover_image.files[0];
        const games = readCollection();

        if (!title) return feedback(message, "Vul de naam van de game in.", true);
        if (!Number.isInteger(year) || year < 1950 || year > 2100) return feedback(message, "Vul een geldig releasejaar in.", true);
        if (ratingValue && (!Number.isInteger(Number(ratingValue)) || Number(ratingValue) < 1 || Number(ratingValue) > 10)) {
          return feedback(message, "Kies een beoordeling tussen 1 en 10.", true);
        }
        if (image && !["image/jpeg", "image/png", "image/webp", "image/gif"].includes(image.type)) {
          return feedback(message, "Kies een JPG-, PNG-, WebP- of GIF-afbeelding.", true);
        }
        if (image && image.size > 3 * 1024 * 1024) return feedback(message, "De afbeelding mag maximaal 3 MB zijn.", true);
        if (games.some((game) =>
          String(game.id) !== String(editingGame?.id) &&
          String(game.title).toLocaleLowerCase("nl") === title.toLocaleLowerCase("nl") &&
          Number(game.release_year) === year
        )) return feedback(message, "Deze game met dit releasejaar staat al in je collectie.", true);

        const game = {
          id: editingGame?.id ?? Date.now(),
          title,
          platform: fields.platform.value,
          genre: fields.genre.value,
          status: fields.status.value,
          release_year: year,
          rating: ratingValue ? Number(ratingValue) : "",
          rating_scale: 10,
          notes: fields.notes.value.trim(),
          image_data: editingGame?.image_data || "",
        };
        submit.disabled = true;
        submit.textContent = image ? "Omslag uploaden..." : "Opslaan...";
        try {
          if (image) {
            const data = new FormData();
            data.append("cover_image", image);
            const response = await fetch("upload-image.php", { method: "POST", body: data });
            const result = await response.json();
            if (!response.ok) throw new Error(result.error || "De afbeelding kon niet worden geüpload.");
            game.image_data = result.path;
          }
          const updated = editingGame
            ? games.map((item) => String(item.id) === String(editingGame.id) ? game : item)
            : [...games, game];
          if (!writeCollection(updated)) throw new Error("Browseropslag is niet beschikbaar. Controleer de opslaginstellingen van je browser.");
          window.location.href = "05-overzicht-games.php";
        } catch (error) {
          feedback(message, `Opslaan is niet gelukt: ${error.message}`, true);
          submit.disabled = false;
          submit.innerHTML = editingGame ? "Wijzigingen opslaan <span>→</span>" : "Game opslaan <span>→</span>";
        }
      });
    }
  }

  if (page === "detail") {
    const gameId = new URLSearchParams(window.location.search).get("id");
    const selectedCatalogGame = catalog.find((game) => String(game.id) === String(gameId)) || catalog[0];
    const personalGame = readCollection().find((game) => String(game.id) === String(gameId));
    const game = personalGame || selectedCatalogGame;
    const title = document.getElementById("detail-title");
    const description = document.getElementById("detail-description");
    const addButton = document.getElementById("detail-add");
    const message = document.getElementById("detail-feedback");

    if (game && title) {
      title.textContent = game.title;
      description.textContent = game.notes || "Voeg deze game toe aan je collectie en schrijf je eigen notities.";
      document.getElementById("detail-platform").textContent = game.platform || "Niet opgegeven";
      document.getElementById("detail-genre").textContent = game.genre || "Niet opgegeven";
      document.getElementById("detail-year").textContent = game.release_year || "Niet opgegeven";
      document.getElementById("detail-status").textContent = statusLabel(game.status);
      if (game.rating) document.querySelector(".detail-rating").innerHTML = `<span>★★★★★</span> ${escapeHtml(game.rating)}<small> / 10</small>`;
      const cover = document.querySelector(".detail-art");
      const coverTitle = cover?.querySelector(".cover-title");
      if (coverTitle) coverTitle.textContent = game.title;
      const coverLabel = cover?.querySelector(".cover-label");
      if (coverLabel) coverLabel.textContent = `${game.genre || "Game"} · ${game.release_year || ""}`;
      if (cover && !personalGame) {
        const knownTone = artTone(game);
        cover.className = `detail-art cover-${escapeHtml(knownTone)}`;
      }
      if (cover && personalGame?.image_data) {
        const image = document.createElement("img");
        image.src = personalGame.image_data;
        image.alt = "";
        cover.prepend(image);
      }
      if (personalGame) {
        addButton.textContent = "✓ Staat in je collectie";
        addButton.disabled = true;
        addButton.classList.remove("button-primary");
        addButton.classList.add("button-quiet");
        const detailsActions = document.querySelector(".detail-actions");
        const editLink = document.createElement("a");
        editLink.className = "button button-quiet";
        editLink.href = `07-updaten.php?id=${encodeURIComponent(personalGame.id)}`;
        editLink.textContent = "Bewerken";
        detailsActions?.append(editLink);
      }
    }

    addButton?.addEventListener("click", () => {
      if (!game) return;
      const games = readCollection();
      const existing = games.find((item) => String(item.id) === String(game.id));
      if (existing) return feedback(message, "Deze game staat al in je collectie.");
      const itemToAdd = { ...game, id: game.id, notes: "", status: "Wishlist" };
      if (!writeCollection([...games, itemToAdd])) return feedback(message, "Toevoegen is niet gelukt. Controleer de beschikbare browseropslag.", true);
      feedback(message, "Game toegevoegd aan je collectie.");
      addButton.textContent = "✓ Toegevoegd aan je collectie";
      addButton.disabled = true;
    });
  }

  if (page === "login") {
    const form = document.getElementById("login-form");
    const message = document.getElementById("login-feedback");
    const modeButton = document.querySelector("[data-auth-mode]");
    let mode = "login";

    const setMode = (nextMode) => {
      mode = nextMode;
      const registering = mode === "register";
      document.getElementById("auth-title").textContent = registering ? "Maak je account." : "Log in.";
      document.getElementById("auth-description").textContent = registering
        ? "Maak een demo-profiel aan om je collectie op dit apparaat bij te houden."
        : "Fijn dat je er weer bent. Waar gaan we vandaag spelen?";
      document.getElementById("login-submit").innerHTML = registering ? "Demo-account maken <span>→</span>" : "Inloggen <span>→</span>";
      document.querySelector("#auth-switch").innerHTML = registering
        ? 'Al een demo-account? <button type="button" data-auth-mode="login">Inloggen</button>'
        : 'Nog geen account? <button type="button" data-auth-mode="register">Account aanmaken</button>';
      document.getElementById("login-password").autocomplete = registering ? "new-password" : "current-password";
      document.getElementById("login-password").value = "";
      message.hidden = true;
      document.querySelector("[data-auth-mode]")?.addEventListener("click", (event) => setMode(event.currentTarget.dataset.authMode), { once: true });
    };
    modeButton?.addEventListener("click", (event) => setMode(event.currentTarget.dataset.authMode));

    const derivePassword = async (password, salt) => {
      if (!window.crypto?.subtle) throw new Error("Deze browser ondersteunt geen veilige wachtwoordcontrole.");
      const key = await window.crypto.subtle.importKey(
        "raw",
        new TextEncoder().encode(password),
        "PBKDF2",
        false,
        ["deriveBits"]
      );
      const bits = await window.crypto.subtle.deriveBits({
        name: "PBKDF2",
        salt,
        iterations: 160000,
        hash: "SHA-256",
      }, key, 256);
      return [...new Uint8Array(bits)].map((byte) => byte.toString(16).padStart(2, "0")).join("");
    };
    const toHex = (bytes) => [...bytes].map((byte) => byte.toString(16).padStart(2, "0")).join("");
    const fromHex = (value) => new Uint8Array(value.match(/.{2}/g).map((byte) => parseInt(byte, 16)));

    form?.addEventListener("submit", async (event) => {
      event.preventDefault();
      message.hidden = true;
      if (!form.reportValidity()) return;
      const email = form.elements.email.value.trim().toLocaleLowerCase("nl");
      const password = form.elements.password.value;
      try {
        const profile = JSON.parse(localStorage.getItem(profileKey) || "null");
        if (mode === "register") {
          if (profile?.email === email) return feedback(message, "Voor dit e-mailadres bestaat al een demo-account. Log in.", true);
          if (profile?.email) return feedback(message, "Er bestaat al een lokaal demo-profiel voor een ander e-mailadres. Log daarmee in.", true);
          const salt = window.crypto.getRandomValues(new Uint8Array(16));
          const passwordHash = await derivePassword(password, salt);
          localStorage.setItem(profileKey, JSON.stringify({ email, salt: toHex(salt), passwordHash }));
        } else {
          if (profile?.email !== email || !profile?.salt || !profile?.passwordHash) {
            return feedback(message, "Geen demo-account gevonden voor dit e-mailadres. Maak eerst een account aan.", true);
          }
          const passwordHash = await derivePassword(password, fromHex(profile.salt));
          if (passwordHash !== profile.passwordHash) return feedback(message, "Het wachtwoord klopt niet. Probeer het opnieuw.", true);
        }
      } catch (error) {
        console.error("GameSpark demo-profiel kon niet worden opgeslagen.", error);
        return feedback(message, `Inloggen is niet gelukt: ${error.message}`, true);
      }
      window.location.href = "05-overzicht-games.php";
    });
  }

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
