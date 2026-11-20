const storageKey = "gamespark-games";
const form = document.getElementById("game-form");
const modal = document.getElementById("modal");
const detailsModal = document.getElementById("details-modal");
const gameRows = document.getElementById("games");
const message = document.getElementById("message");
const formError = document.getElementById("form-error");
const platformOptions = [
  "PC",
  "PS5",
  "PS4",
  "Xbox",
  "Xbox Series X/S",
  "Xbox One",
  "Nintendo Switch",
  "Nintendo Switch 2",
  "Steam Deck",
  "Mobile",
];
const genreOptions = ["Action", "Adventure", "RPG", "Strategy", "Simulation"];

let games = [];
let editingGame = null;

try {
  games = JSON.parse(localStorage.getItem(storageKey) || "[]");
  if (!Array.isArray(games)) games = [];
} catch {
  message.textContent = "Opgeslagen games konden niet worden gelezen.";
  message.hidden = false;
}

function escapeHtml(text) {
  return String(text).replace(/[&<>"']/g, (character) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  })[character]);
}

function statusName(status) {
  return {
    Wishlist: "Wil ik spelen",
    "Speelt nu": "Speel ik nu",
  }[status] || status;
}

function updateFilters(id, values, label) {
  const select = document.getElementById(id);
  const selected = select.value;
  select.innerHTML = `<option value="">Alle ${label}</option>` +
    [...new Set(values)].sort().map((value) =>
      `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`
    ).join("");
  select.value = selected;
}

function showGames() {
  let visibleGames = games.filter((game) =>
    (!document.getElementById("filter-platform").value || game.platform === document.getElementById("filter-platform").value) &&
    (!document.getElementById("filter-genre").value || game.genre === document.getElementById("filter-genre").value) &&
    (!document.getElementById("filter-status").value || game.status === document.getElementById("filter-status").value)
  );

  const sortBy = document.getElementById("sort-games").value;
  visibleGames = visibleGames.slice().sort((a, b) => sortBy === "title"
    ? a.title.localeCompare(b.title)
    : Number(a[sortBy]) - Number(b[sortBy]));

  gameRows.innerHTML = visibleGames.map((game) => {
    const cover = escapeHtml(game.image_data || "assets/fallback.svg");
    return `<tr>
      <td><img class="cover" src="${cover}" alt=""> ${escapeHtml(game.title)}</td>
      <td>${escapeHtml(game.platform)}</td>
      <td>${escapeHtml(game.genre)}</td>
      <td>${escapeHtml(statusName(game.status))}</td>
      <td>${escapeHtml(game.release_year)}</td>
      <td>${escapeHtml(game.rating)} / 5</td>
      <td>${escapeHtml(game.notes || "—")}</td>
      <td>
        <button data-details="${game.id}" type="button">Details</button>
        <button data-edit="${game.id}" type="button">Bewerk</button>
        <button data-delete="${game.id}" type="button">Verwijder</button>
      </td>
    </tr>`;
  }).join("");

  const emptyState = document.getElementById("empty-state");
  emptyState.textContent = games.length
    ? "Geen games gevonden."
    : "Je bibliotheek is leeg. Voeg je eerste game toe.";
  emptyState.hidden = visibleGames.length > 0;
}

function saveGames(updatedGames) {
  localStorage.setItem(storageKey, JSON.stringify(updatedGames));
  games = updatedGames;
  updateFilters("filter-platform", [...platformOptions, ...games.map((game) => game.platform)], "platforms");
  updateFilters("filter-genre", [...genreOptions, ...games.map((game) => game.genre)], "genres");
  showGames();
}

function openForm(game) {
  editingGame = game;
  form.reset();
  formError.hidden = true;
  document.getElementById("form-title").textContent = game ? "Game bewerken" : "Game toevoegen";

  if (game) {
    form.elements.title.value = game.title;
    form.elements.platform.value = game.platform;
    form.elements.genre.value = game.genre;
    form.elements.status.value = game.status;
    form.elements.release_year.value = game.release_year;
    form.elements.rating.value = game.rating;
    form.elements.notes.value = game.notes;
  }

  modal.hidden = false;
  form.elements.title.focus();
}

function closeForm() {
  modal.hidden = true;
  form.reset();
  editingGame = null;
}

function showDetails(game) {
  document.getElementById("details-title").textContent = game.title;
  document.getElementById("details-image").src = game.image_data || "assets/fallback.svg";
  document.getElementById("details-platform").textContent = game.platform;
  document.getElementById("details-genre").textContent = game.genre;
  document.getElementById("details-status").textContent = statusName(game.status);
  document.getElementById("details-year").textContent = game.release_year;
  document.getElementById("details-rating").textContent = `${game.rating} / 5`;
  document.getElementById("details-notes").textContent = game.notes || "—";
  detailsModal.hidden = false;
}

function readImage(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = () => reject(new Error("Afbeelding lezen is mislukt."));
    reader.readAsDataURL(file);
  });
}

document.getElementById("add-game").addEventListener("click", () => openForm(null));
document.getElementById("close").addEventListener("click", closeForm);
document.getElementById("cancel").addEventListener("click", closeForm);
document.getElementById("close-details").addEventListener("click", () => {
  detailsModal.hidden = true;
});

for (const id of ["filter-platform", "filter-genre", "filter-status", "sort-games"]) {
  document.getElementById(id).addEventListener("change", showGames);
}

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  const fields = form.elements;
  const image = fields.cover_image.files[0];
  const title = fields.title.value.trim();
  const year = Number(fields.release_year.value);
  const rating = Number(fields.rating.value);
  const validStatuses = ["Wishlist", "In bezit", "Speelt nu", "Uitgespeeld"];

  if (!title) {
    formError.textContent = "Vul de gametitel in.";
    formError.hidden = false;
    return;
  }
  if (!Number.isInteger(year) || year < 1950 || year > 2100) {
    formError.textContent = "Vul een geldig releasejaar in.";
    formError.hidden = false;
    return;
  }
  if (!Number.isInteger(rating) || rating < 1 || rating > 5 || !validStatuses.includes(fields.status.value)) {
    formError.textContent = "Kies een geldige status en rating.";
    formError.hidden = false;
    return;
  }
  if (image && !["image/jpeg", "image/png", "image/webp", "image/gif"].includes(image.type)) {
    formError.textContent = "Kies een JPG-, PNG-, WebP- of GIF-afbeelding.";
    formError.hidden = false;
    return;
  }
  if (image && image.size > 3 * 1024 * 1024) {
    formError.textContent = "De afbeelding mag maximaal 3 MB zijn.";
    formError.hidden = false;
    return;
  }

  const game = {
    id: editingGame ? editingGame.id : Date.now(),
    title,
    platform: fields.platform.value.trim(),
    genre: fields.genre.value.trim(),
    status: fields.status.value,
    release_year: year,
    rating,
    notes: fields.notes.value.trim(),
    image_data: editingGame ? editingGame.image_data : "",
  };

  if (games.some((item) =>
    item.id !== game.id &&
    item.title.toLowerCase() === game.title.toLowerCase() &&
    Number(item.release_year) === game.release_year
  )) {
    formError.textContent = "Deze game met dit releasejaar bestaat al.";
    formError.hidden = false;
    return;
  }

  try {
    if (image) game.image_data = await readImage(image);
    const updatedGames = editingGame
      ? games.map((item) => item.id === game.id ? game : item)
      : [...games, game];
    saveGames(updatedGames);
    closeForm();
  } catch {
    formError.textContent = "Opslaan is niet gelukt. Misschien is de browseropslag vol.";
    formError.hidden = false;
  }
});

gameRows.addEventListener("click", (event) => {
  const button = event.target.closest("button");
  if (!button) return;

  const id = button.dataset.details || button.dataset.edit || button.dataset.delete;
  const game = games.find((item) => item.id === Number(id));
  if (!game) return;

  if (button.dataset.details) {
    showDetails(game);
  } else if (button.dataset.edit) {
    openForm(game);
  } else if (button.dataset.delete && confirm(`Game "${game.title}" verwijderen?`)) {
    try {
      saveGames(games.filter((item) => item.id !== game.id));
    } catch {
      message.textContent = "Verwijderen is niet gelukt.";
      message.hidden = false;
    }
  }
});

updateFilters("filter-platform", [...platformOptions, ...games.map((game) => game.platform)], "platforms");
updateFilters("filter-genre", [...genreOptions, ...games.map((game) => game.genre)], "genres");
showGames();
