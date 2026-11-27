const navigationLinks = [
  ["Home", "01-home.php", "Home"],
  ["Games", "02-games.php", "Games"],
  ["Over ons", "03-over-ons.php", "Over ons"],
  ["Login", "04-login.php", "Login"],
  ["Overzicht", "05-overzicht-games.php", "Overzicht"],
  ["Aanmaken", "06-aanmaken.php", "Aanmaken"],
  ["Updaten", "07-updaten.php", "Updaten"],
  ["Contact", "08-contact.php", "Contact"],
  ["Details", "09-detail-game.php", "Details"],
];
const nav = (active) => `<div class="hd"><a class="logo" href="01-home.php"><span class="pad"></span>GameSpark</a><nav class="nav" aria-label="Paginanavigatie">${navigationLinks.map(([label, href, key]) => `<a href="${href}"${key === active ? ' class="on" aria-current="page"' : ""}>${label}</a>`).join("")}</nav><span class="user"></span></div>`;
const footer = `<div class="ft"><b>GameSpark</b><span>Over ons &nbsp;|&nbsp; Contact</span></div>`;
const placeholder = (height, modifier = "") => `<div class="ph ph-${height}${modifier ? ` ${modifier}` : ""}"></div>`;
const card = (name, rating, height = 70) => `<div class="card">${placeholder(height, "ph-card")}<div class="c"><span>${name}</span><span>★ ${rating}</span></div></div>`;
const field = (label, value = "", modifier = "") => `<div class="row"><span>${label}</span><div class="in ${modifier}">${value}</div></div>`;
const page = (tab, active, body) => `<section><span class="tab">${tab}</span><div class="f">${nav(active)}<div class="bd">${body}</div>${footer}</div></section>`;
const loginNotice = `<div class="lock"><span>🔒 Je moet ingelogd zijn om deze pagina te bekijken en te gebruiken.</span><span class="btn">Inloggen</span></div>`;
const filters = ["Platform", "Genre", "Status", "Beoordeling"];
const games = [["Red Dead Redemption 2", "9.4"], ["God of War", "9.6"], ["Minecraft", "9.1"], ["GTA VI", "9.7"], ["The Witcher 3", "9.4"], ["Elden Ring", "9.5"]];
const pages = [];

pages.push(page("1. Home", "Home", `
<div class="r2 hero">${placeholder(130)}<div><span class="btn">▶ Nu spelen</span><h2>Red Dead Redemption 2</h2><div class="sb">Een epische reis door het Wilde Westen.</div><div>★ 9.4/10 | Singleplayer</div><span class="btn">Game bekijken ›</span></div></div>
<div class="section-title-row"><h3>Binnenkort te spelen</h3><span class="sb">Bekijk meer →</span></div>
<div class="cols cols-three">${["Hollow Knight: Silksong", "GTA VI", "Ghost of Yōtei"].map((name) => `<div class="card">${placeholder(40, "ph-card")}<div class="c">${name}</div></div>`).join("")}</div>
<div class="section-title-row"><h3>Populaire games</h3><span class="sb">Bekijk meer →</span></div>
<div class="cols cols-five">${["Minecraft", "Terraria", "Stardew", "Elden Ring", "Witcher 3"].map((name) => card(name, "9.x", 32)).join("")}</div>`));

pages.push(page("2. Games", "Games", `
<div class="r2 games-layout"><div class="side games-sidebar"><h2>Genres</h2>${["Actie", "RPG", "Survival", "Strategie", "Simulatie", "Sport", "Racing", "Puzzle", "Platformer", "Avontuur"].map((genre) => `<div>${genre}<span>›</span></div>`).join("")}</div>
<div class="games-content"><div class="sp"><h2>Alle games</h2><span>Sorteren op: <span class="btn">Populariteit ▾</span></span></div>
<div class="cols cols-three">${games.map((game) => card(game[0], game[1], 56)).join("")}</div></div></div>`));

pages.push(page("3. Over ons", "Over ons", `
<div class="r2 about-layout">${placeholder(190)}<div><h2>Over GameSpark</h2><p>GameSpark is dé plek voor gamers. Ontdek de nieuwste games, volg je favoriete titels en blijf op de hoogte van het laatste nieuws in de gaming wereld.</p><hr>
<div class="cols feature-grid">${["Groot aanbod aan games", "Altijd up-to-date", "Een actieve community"].map((text) => `<div><div class="ph feature-icon"></div>${text}</div>`).join("")}</div></div></div>`));

pages.push(page("4. Login", "Login", `
<div class="login-panel"><h2 class="login-title">Inloggen</h2><div class="sb login-intro">Log in om je gamecollectie te beheren.</div>
<div>E-mailadres *</div><div class="in">naam@voorbeeld.nl</div><div class="login-password-label">Wachtwoord *</div><div class="in">••••••••</div>
<div class="sp login-options"><span>☐ Onthoud mij</span><span class="forgot-password">Wachtwoord vergeten?</span></div>
<div class="btn login-submit">Inloggen</div><div class="sb login-register">Nog geen account? <u>Registreren</u></div></div>`));

const tableRow = (name, platform, status, rating, editable) => `<tr><td>${placeholder(34)}</td><td>${name}</td><td>${platform}</td><td>${status}</td><td>${rating}</td><td><span class="btn">Bekijken</span>${editable ? '<span class="btn">Bewerken</span>' : ""}<span class="btn">Verwijderen</span></td></tr>`;
pages.push(page("5. Overzicht van games", "Overzicht", `${loginNotice}
<div class="sp"><h2>Games</h2><span class="btn">+ Game toevoegen</span></div>
<div class="r2">${["Alle platforms", "Alle genres", "Alle statussen", "Alle scores"].map((label, index) => `<div>${filters[index]}<div class="in">${label} ▾</div></div>`).join("")}</div>
<table class="wireframe-table"><tr><th>Afbeelding</th><th>Naam</th><th>Platform</th><th>Status</th><th>Rating</th><th>Acties</th></tr>${tableRow("God of War", "PlayStation", "In bezit", "9/10", true)}${tableRow("Hollow Knight", "PC", "Speelt nu", "8/10", true)}${tableRow("Stardew Valley", "PC", "Uitgespeeld", "9/10", true)}</table>
<div class="hint">Pagina 1 van 3 &nbsp; ‹ 1 2 3 ›</div>`));

const form = (title, populated, submitLabel) => `${populated ? loginNotice : ""}<div class="sb">← Terug naar games</div><h2>${title}</h2>
${field("Naam game *", populated ? "God of War" : "")}${field("Afbeelding *", populated ? "god-of-war.jpg &nbsp; ⬆ Wijzigen" : "Geen bestand &nbsp; ⬆ Uploaden")}${field("Platform *", populated ? "PlayStation ▾" : "Selecteer platform ▾")}${field("Genre *", populated ? "Actie ▾" : "Selecteer genre ▾")}${field("Releasejaar *", populated ? "2018" : "")}${field("Status *", populated ? "In bezit ▾" : "Selecteer status ▾")}${field("Beoordeling (1-10) *", populated ? "9" : "")}
<div class="form-actions"><span class="btn">${submitLabel}</span><span class="btn">Annuleren</span>${populated ? '<span class="btn float-end">Verwijderen</span>' : ""}</div>`;
pages.push(page("6. Aanmaken", "Aanmaken", form("Game toevoegen", false, "Opslaan")));
pages.push(page("7. Updaten", "Updaten", form("Game bewerken", true, "Wijzigingen opslaan")));

pages.push(page("8. Contact", "Contact", `
<div class="r2"><div class="contact-form"><h2>Contact</h2><div class="sb">Heb je een vraag of suggestie? Stuur ons een bericht.</div>${field("Naam *")}${field("E-mailadres *")}${field("Onderwerp *", "Selecteer onderwerp ▾")}${field("Bericht *", "", "t")}<span class="btn">Verzenden</span><span class="btn">Annuleren</span></div>
<div class="contact-details"><h3 class="contact-heading">Contactgegevens</h3><p><b>E-mail</b><br>info@gamespark.nl</p><p><b>Telefoon</b><br>+31 6 12 34 56 78</p><p><b>Adres</b><br>Gamestraat 1, Bergen op Zoom</p><div class="hint">We reageren binnen 2 werkdagen.</div></div></div>`));

pages.push(page("9. Detailpagina per game", "Details", `
<div class="sb">← Terug naar games</div>
<div class="r2 detail-layout">${placeholder(170, "detail-cover")}<div class="detail-info"><h2>God of War</h2><div>★ 9.6/10 &nbsp;|&nbsp; Singleplayer</div><div class="sb detail-meta">Platform: PlayStation<br>Genre: Actie, Avontuur<br>Release: 2018<br>Status: In bezit</div><span class="btn">Bewerken</span><span class="btn">Verwijderen</span></div></div>
<h3>Beschrijving</h3><div class="sb">Korte omschrijving van de game over Kratos en zijn zoon op reis door de Noorse wereld.</div>
<h3>Screenshots</h3><div class="cols detail-screenshots">${placeholder(44)}${placeholder(44)}${placeholder(44)}${placeholder(44)}</div>
<h3>Vergelijkbare games</h3><div class="cols cols-four">${["Elden Ring", "GTA VI", "Witcher 3", "Minecraft"].map((name) => card(name, "9.x", 30)).join("")}</div>`));

const container = document.getElementById("g");
const pageIndex = Number(container.dataset.page);
container.innerHTML = pages[pageIndex];
