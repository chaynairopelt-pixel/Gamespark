<?php
declare(strict_types=1);

$catalog = [
    ['id' => 'red-dead-redemption-2', 'title' => 'Red Dead Redemption 2', 'platform' => 'PlayStation', 'genre' => 'Avontuur', 'release_year' => 2018, 'rating' => 9.4, 'status' => 'In bezit', 'notes' => 'Een epische reis door het Wilde Westen.', 'tone' => 'sunset'],
    ['id' => 'god-of-war', 'title' => 'God of War', 'platform' => 'PlayStation', 'genre' => 'Actie', 'release_year' => 2018, 'rating' => 9.6, 'status' => 'Uitgespeeld', 'notes' => 'Een avontuurlijke reis door de Noorse mythologie.', 'tone' => 'ice'],
    ['id' => 'minecraft', 'title' => 'Minecraft', 'platform' => 'PC', 'genre' => 'Sandbox', 'release_year' => 2011, 'rating' => 9.1, 'status' => 'Speelt nu', 'notes' => 'Bouw, ontdek en maak je eigen wereld.', 'tone' => 'forest'],
    ['id' => 'gta-vi', 'title' => 'Grand Theft Auto VI', 'platform' => 'PlayStation 5', 'genre' => 'Actie', 'release_year' => 2026, 'rating' => 9.7, 'status' => 'Wishlist', 'notes' => 'Een nieuw avontuur in Vice City.', 'tone' => 'neon'],
    ['id' => 'the-witcher-3', 'title' => 'The Witcher 3', 'platform' => 'PC', 'genre' => 'RPG', 'release_year' => 2015, 'rating' => 9.4, 'status' => 'In bezit', 'notes' => 'Een uitgestrekte fantasywereld vol verhalen.', 'tone' => 'violet'],
    ['id' => 'elden-ring', 'title' => 'Elden Ring', 'platform' => 'PlayStation 5', 'genre' => 'RPG', 'release_year' => 2022, 'rating' => 9.5, 'status' => 'In bezit', 'notes' => 'Verken de mysterieuze Lands Between.', 'tone' => 'gold'],
    ['id' => 'hollow-knight-silksong', 'title' => 'Hollow Knight: Silksong', 'platform' => 'Nintendo Switch', 'genre' => 'Platformer', 'release_year' => 2025, 'rating' => 9.2, 'status' => 'Wishlist', 'notes' => 'Een sfeervol avontuur door een nieuwe wereld.', 'tone' => 'pink'],
    ['id' => 'stardew-valley', 'title' => 'Stardew Valley', 'platform' => 'PC', 'genre' => 'Simulatie', 'release_year' => 2016, 'rating' => 9.3, 'status' => 'In bezit', 'notes' => 'Begin een nieuw leven op je eigen boerderij.', 'tone' => 'meadow'],
    ['id' => 'ghost-of-yotei', 'title' => 'Ghost of Yōtei', 'platform' => 'PlayStation 5', 'genre' => 'Avontuur', 'release_year' => 2025, 'rating' => 9.0, 'status' => 'Wishlist', 'notes' => 'Een filmisch avontuur in het noorden van Japan.', 'tone' => 'rose'],
];

$pageNames = [
    'home' => ['GameSpark — Ontdek je volgende avontuur', 'Home'],
    'games' => ['Ontdek games — GameSpark', 'Games'],
    'about' => ['Over GameSpark', 'Over ons'],
    'login' => ['Inloggen — GameSpark', 'Inloggen'],
    'collection' => ['Mijn collectie — GameSpark', 'Mijn collectie'],
    'create' => ['Game toevoegen — GameSpark', 'Game toevoegen'],
    'edit' => ['Game bewerken — GameSpark', 'Game bewerken'],
    'contact' => ['Contact — GameSpark', 'Contact'],
    'detail' => ['Game details — GameSpark', 'Gamedetails'],
];
$page = isset($pageNames[$page ?? '']) ? $page : 'home';
$title = $pageNames[$page][0];
$activePage = $page === 'detail' ? 'games' : ($page === 'create' || $page === 'edit' ? 'collection' : $page);
$selectedGame = null;
if ($page === 'detail') {
    foreach ($catalog as $game) {
        if ($game['id'] === ($_GET['id'] ?? 'god-of-war')) {
            $selectedGame = $game;
            break;
        }
    }
    $selectedGame = $selectedGame ?? $catalog[0];
}

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function game_card(array $game, string $extraClass = ''): void
{
    ?>
    <article class="game-card <?= e($extraClass) ?>">
      <a class="game-cover cover-<?= e($game['tone'] ?? 'blue') ?>" href="09-detail-game.php?id=<?= e($game['id']) ?>" aria-label="Bekijk <?= e($game['title']) ?>">
        <span class="cover-orbit" aria-hidden="true"></span>
        <span class="cover-label"><?= e($game['genre']) ?> <span>·</span> <?= e($game['release_year']) ?></span>
        <span class="cover-title"><?= e($game['title']) ?></span>
        <span class="cover-arrow" aria-hidden="true">↗</span>
      </a>
      <div class="game-card-content">
        <div class="game-name-line">
          <a href="09-detail-game.php?id=<?= e($game['id']) ?>"><?= e($game['title']) ?></a>
          <span class="score">★ <?= e($game['rating']) ?></span>
        </div>
        <p><?= e($game['platform']) ?> <span>·</span> <?= e($game['genre']) ?></p>
        <a class="text-link" href="09-detail-game.php?id=<?= e($game['id']) ?>">Bekijk game <span aria-hidden="true">→</span></a>
      </div>
    </article>
    <?php
}
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#11111b">
  <meta name="description" content="GameSpark — ontdek games en beheer je persoonlijke gamecollectie.">
  <title><?= e($title) ?></title>
  <link rel="stylesheet" href="css/styles.css">
  <script>
    window.gameSparkCatalog = <?= json_encode($catalog, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  </script>
  <script src="js/app.js" defer></script>
</head>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page-shell" data-page="<?= e($page) ?>">
    <?php if ($page === 'home'): ?>
      <section class="hero">
        <div class="hero-copy">
          <span class="eyebrow"><span class="pulse-dot"></span> JOUW GAMING UNIVERSUM</span>
          <h1>Vind jouw<br><span>volgende avontuur.</span></h1>
          <p>Ontdek games waar je blij van wordt. Verzamel je favorieten en houd bij wat je speelt.</p>
          <div class="hero-actions"><a class="button button-primary" href="02-games.php">Ontdek games <span>↗</span></a><a class="button button-quiet" href="05-overzicht-games.php">Mijn collectie</a></div>
          <div class="hero-proof"><span class="proof-stars">★★★★★</span><span>Een plek voor elke gamer</span><span class="proof-divider"></span><span><?= count($catalog) ?> games om te ontdekken</span></div>
        </div>
        <a class="hero-feature cover-sunset" href="09-detail-game.php?id=red-dead-redemption-2">
          <span class="hero-feature-glow"></span><span class="feature-topline"><span class="live-tag">★ TOPKEUZE</span><span>01 / 09</span></span>
          <span class="feature-art-symbol" aria-hidden="true">✦</span>
          <span class="feature-bottom"><span class="feature-category">AVONTUUR · 2018</span><strong>Red Dead<br>Redemption 2</strong><span class="feature-rating">★ 9.4 <small>/ 10</small></span><span class="feature-link">Bekijk de game <b>↗</b></span></span>
        </a>
        <div class="hero-orb orb-one"></div><div class="hero-orb orb-two"></div>
      </section>
      <section class="home-section">
        <div class="section-heading"><div><span class="eyebrow">JOUW VOLGENDE FAVORIET</span><h2>Populair op GameSpark</h2></div><a class="text-link" href="02-games.php">Bekijk alle games <span>→</span></a></div>
        <div class="game-grid home-grid"><?php foreach (array_slice($catalog, 1, 4) as $game) { game_card($game); } ?></div>
      </section>
      <section class="collection-promo"><div class="promo-icon">✳</div><div><span class="eyebrow">HOUD JE AVONTUREN BIJ</span><h2>Jouw collectie, jouw verhaal.</h2><p>Bewaar je games op één plek, geef ze een rating en voeg je eigen notities toe.</p></div><a class="button button-primary" href="05-overzicht-games.php">Start je collectie <span>→</span></a></section>

    <?php elseif ($page === 'games'): ?>
      <section class="page-intro"><span class="eyebrow">DE GAMEBIBLIOTHEEK</span><h1>Ontdek je <span>volgende game.</span></h1><p>Van grote avonturen tot kleine parels — er is altijd iets nieuws te spelen.</p></section>
      <section class="catalog-layout">
        <aside class="genre-panel"><h2>Genres</h2><p>Kies een wereld om in te duiken.</p><div class="genre-list">
          <?php foreach (['Alles', 'Actie', 'Avontuur', 'RPG', 'Sandbox', 'Simulatie', 'Platformer'] as $genre): ?><button class="genre-filter<?= $genre === 'Alles' ? ' is-selected' : '' ?>" type="button" data-genre="<?= e($genre) ?>"><?= e($genre) ?><span>↗</span></button><?php endforeach; ?>
        </div><div class="sidebar-note"><span>✦</span><p>De mooiste avonturen beginnen met een ontdekking.</p></div></aside>
        <div class="catalog-main"><div class="catalog-toolbar"><div><span class="eyebrow">KIES JE AVONTUUR</span><h2>Alle games <span class="results-count">(<?= count($catalog) ?>)</span></h2></div><label class="sort-control">Sorteren<select id="catalog-sort"><option value="popular">Populairst</option><option value="title">Naam A–Z</option><option value="year">Nieuwste eerst</option><option value="rating">Hoogste rating</option></select></label></div>
          <div class="game-grid catalog-grid" id="catalog-grid"><?php foreach ($catalog as $game) { game_card($game); } ?></div>
          <p class="no-results" id="catalog-empty" hidden>Geen games in dit genre gevonden.</p>
        </div>
      </section>

    <?php elseif ($page === 'about'): ?>
      <section class="about-hero"><div class="about-art"><span class="about-art-ring"></span><span class="about-art-star">✳</span><span class="about-art-caption">PLAY<br>YOUR<br>WAY</span><span class="about-art-coord">GS · EST. 2025</span></div><div class="about-copy"><span class="eyebrow">OVER GAMESPARK</span><h1>Games zijn beter<br><span>samen ontdekt.</span></h1><p>GameSpark is jouw persoonlijke uitvalsbasis in de wereld van games. Ontdek nieuwe titels, houd je eigen collectie bij en vergeet nooit wat je nog wilde spelen.</p><p>Geen ingewikkelde ranglijsten of ruis — gewoon een fijne plek voor alles wat jij graag speelt.</p><a class="button button-primary" href="02-games.php">Ontdek de bibliotheek <span>↗</span></a></div></section>
      <section class="values-grid"><article><span class="value-icon">◈</span><h2>Genoeg te ontdekken</h2><p>Een zorgvuldig gekozen mix van avonturen, klassiekers en nieuwe favorieten.</p></article><article><span class="value-icon">✳</span><h2>Jouw plek, jouw lijst</h2><p>Maak je eigen collectie en geef elke game een persoonlijke beoordeling.</p></article><article><span class="value-icon">⌁</span><h2>Voor iedere speler</h2><p>Of je nu elke avond speelt of af en toe op avontuur gaat.</p></article></section>

    <?php elseif ($page === 'login'): ?>
      <section class="auth-layout"><div class="auth-aside cover-violet"><span class="eyebrow">SAVE YOUR PROGRESS</span><span class="auth-aside-mark">✳</span><h2>Je volgende<br>avontuur wacht.</h2><p>Log in om je gamecollectie in deze browser te bewaren.</p><span class="auth-aside-small">GAMESPARK · PLAYER ONE</span></div><div class="auth-panel"><span class="eyebrow">WELKOM TERUG</span><h1 id="auth-title">Log in.</h1><p id="auth-description">Fijn dat je er weer bent. Waar gaan we vandaag spelen?</p><form id="login-form" novalidate><label for="login-email">E-mailadres</label><input id="login-email" name="email" type="email" autocomplete="email" placeholder="jij@voorbeeld.nl" required><label for="login-password">Wachtwoord</label><input id="login-password" name="password" type="password" autocomplete="current-password" minlength="4" placeholder="Minimaal 4 tekens" required><p class="form-feedback" id="login-feedback" role="status" hidden></p><button class="button button-primary button-wide" type="submit" id="login-submit">Inloggen <span>→</span></button></form><p class="auth-switch" id="auth-switch">Nog geen account? <button type="button" data-auth-mode="register">Account aanmaken</button></p><p class="demo-note">Demo-profiel en collectie worden alleen in deze browser opgeslagen. Er is geen serveraccount of synchronisatie tussen apparaten.</p></div></section>

    <?php elseif ($page === 'collection'): ?>
      <section class="page-intro compact-intro"><span class="eyebrow">JOUW PERSOONLIJKE BIBLIOTHEEK</span><h1>Mijn <span>collectie.</span></h1><p>Alles wat je speelt, wilt spelen of nooit wilt vergeten.</p></section>
      <div class="collection-toolbar"><div class="collection-stat"><strong id="collection-count">0</strong><span>games in je collectie</span></div><a class="button button-primary" href="06-aanmaken.php"><span>＋</span> Game toevoegen</a></div>
      <section class="filter-bar" aria-label="Collectie filteren"><label>Zoeken<input id="collection-search" type="search" placeholder="Zoek een game..."></label><label>Platform<select id="collection-platform"><option value="">Alle platforms</option></select></label><label>Status<select id="collection-status"><option value="">Alle statussen</option><option value="Wishlist">Wil ik spelen</option><option value="In bezit">In bezit</option><option value="Speelt nu">Speel ik nu</option><option value="Uitgespeeld">Uitgespeeld</option></select></label><label>Sorteren<select id="collection-sort"><option value="title">Naam A–Z</option><option value="release_year">Releasejaar</option><option value="rating">Rating</option></select></label></section>
      <div class="collection-grid" id="my-games"></div><section class="empty-collection" id="collection-empty" hidden><span>✳</span><h2>Je collectie begint hier.</h2><p>Voeg je eerste game toe en maak deze plek helemaal van jou.</p><a class="button button-primary" href="06-aanmaken.php">Voeg een game toe <span>→</span></a></section>

    <?php elseif ($page === 'create' || $page === 'edit'): ?>
      <a class="back-link" href="05-overzicht-games.php">← <span>Terug naar mijn collectie</span></a>
      <section class="form-page"><div class="form-page-heading"><span class="eyebrow"><?= $page === 'edit' ? 'PAS JE COLLECTIE AAN' : 'MAAK HET PERSOONLIJK' ?></span><h1 id="game-form-heading"><?= $page === 'edit' ? 'Game bewerken.' : 'Game toevoegen.' ?></h1><p><?= $page === 'edit' ? 'Pas de details van je game aan.' : 'Bewaar een game en houd je eigen voortgang bij.' ?></p></div><form class="game-form" id="game-form" enctype="multipart/form-data" novalidate><input type="hidden" name="id"><div class="form-columns"><label>Naam van de game <span>*</span><input name="title" maxlength="160" placeholder="Bijv. Hades II" required></label><label>Platform <span>*</span><select name="platform" required><option value="">Kies een platform</option><?php foreach (['PC', 'PlayStation 5', 'PlayStation 4', 'Xbox Series X/S', 'Xbox One', 'Nintendo Switch', 'Steam Deck', 'Mobile'] as $platform): ?><option><?= e($platform) ?></option><?php endforeach; ?></select></label><label>Genre <span>*</span><select name="genre" required><option value="">Kies een genre</option><?php foreach (['Actie', 'Avontuur', 'RPG', 'Sandbox', 'Simulatie', 'Strategie', 'Sport', 'Platformer', 'Racing', 'Puzzle'] as $genre): ?><option><?= e($genre) ?></option><?php endforeach; ?></select></label><label>Releasejaar <span>*</span><input name="release_year" type="number" min="1950" max="2100" placeholder="2025" required></label><label>Status <span>*</span><select name="status" required><option value="">Kies je status</option><option value="Wishlist">Wil ik spelen</option><option value="In bezit">In bezit</option><option value="Speelt nu">Speel ik nu</option><option value="Uitgespeeld">Uitgespeeld</option></select></label><label>Jouw beoordeling <span class="optional">(optioneel)</span><select name="rating"><option value="">Nog geen beoordeling</option><?php for ($rating = 10; $rating >= 1; $rating--): ?><option value="<?= $rating ?>"><?= $rating ?> / 10</option><?php endfor; ?></select></label><label class="upload-field">Omslagafbeelding <span class="optional">(optioneel, max. 3 MB)</span><input name="cover_image" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><span class="upload-help">JPG, PNG, WebP of GIF</span></label><label class="notes-field">Notities <span class="optional">(optioneel)</span><textarea name="notes" rows="4" maxlength="500" placeholder="Wat wil je onthouden over deze game?"></textarea></label></div><p class="form-feedback" id="game-form-feedback" role="alert" hidden></p><div class="form-actions"><a class="button button-quiet" href="05-overzicht-games.php">Annuleren</a><button class="button button-primary" type="submit" id="game-save">Game opslaan <span>→</span></button><button class="button button-danger" type="button" id="game-delete" hidden>Verwijderen</button></div></form></section>

    <?php elseif ($page === 'contact'): ?>
      <section class="page-intro compact-intro"><span class="eyebrow">WE HOREN GRAAG VAN JE</span><h1>Even <span>bijpraten?</span></h1><p>Een vraag, idee of game die we echt moeten proberen? Stuur ons een bericht.</p></section><section class="contact-layout"><form class="contact-form" id="contact-form"><div class="form-columns"><label>Je naam <span>*</span><input name="name" autocomplete="name" required placeholder="Hoe mogen we je noemen?"></label><label>E-mailadres <span>*</span><input name="email" type="email" autocomplete="email" required placeholder="jij@voorbeeld.nl"></label><label class="subject-field">Onderwerp <span>*</span><select name="subject" required><option value="">Kies een onderwerp</option><option>Algemene vraag</option><option>Idee of suggestie</option><option>Probleem met mijn collectie</option><option>Anders</option></select></label><label class="message-field">Je bericht <span>*</span><textarea name="message" rows="6" required placeholder="Vertel ons waar je mee zit..."></textarea></label></div><p class="form-feedback" id="contact-feedback" role="status" hidden></p><button class="button button-primary" type="submit">Verstuur bericht <span>→</span></button></form><aside class="contact-aside"><span class="eyebrow">CONTACTGEGEVENS</span><div class="contact-detail"><span>✉</span><div><small>E-MAIL</small><a href="mailto:info@gamespark.nl">info@gamespark.nl</a></div></div><div class="contact-detail"><span>◷</span><div><small>REACTIETIJD</small><p>We reageren binnen 2 werkdagen.</p></div></div><div class="contact-detail"><span>⌖</span><div><small>GAMESPARK</small><p>Bergen op Zoom, Nederland</p></div></div><div class="contact-aside-art"><span>GG</span><small>LET'S TALK GAMES</small></div></aside></section>

    <?php elseif ($page === 'detail' && $selectedGame !== null): ?>
      <a class="back-link" href="02-games.php">← <span>Terug naar games</span></a><section class="detail-hero"><div class="detail-art cover-<?= e($selectedGame['tone']) ?>"><span class="cover-orbit" aria-hidden="true"></span><span class="detail-art-mark">✳</span><span class="cover-label"><?= e($selectedGame['genre']) ?> <span>·</span> <?= e($selectedGame['release_year']) ?></span><span class="cover-title"><?= e($selectedGame['title']) ?></span></div><div class="detail-copy"><span class="eyebrow">GAMESPARK SELECTIE</span><h1 id="detail-title"><?= e($selectedGame['title']) ?></h1><div class="detail-rating"><span>★★★★★</span> <?= e($selectedGame['rating']) ?><small> / 10</small></div><p id="detail-description"><?= e($selectedGame['notes']) ?></p><dl class="detail-facts"><div><dt>Platform</dt><dd id="detail-platform"><?= e($selectedGame['platform']) ?></dd></div><div><dt>Genre</dt><dd id="detail-genre"><?= e($selectedGame['genre']) ?></dd></div><div><dt>Releasejaar</dt><dd id="detail-year"><?= e($selectedGame['release_year']) ?></dd></div><div><dt>Status</dt><dd id="detail-status"><?= e($selectedGame['status']) ?></dd></div></dl><div class="detail-actions"><button class="button button-primary" type="button" id="detail-add">＋ Toevoegen aan collectie</button><a class="button button-quiet" href="02-games.php">Verder ontdekken</a></div><p class="form-feedback" id="detail-feedback" role="status" hidden></p></div></section>
      <section class="home-section related-section"><div class="section-heading"><div><span class="eyebrow">MISSCHIEN OOK IETS VOOR JOU</span><h2>Meer om te ontdekken</h2></div><a class="text-link" href="02-games.php">Alle games <span>→</span></a></div><div class="game-grid home-grid"><?php foreach (array_slice($catalog, 1, 4) as $game) { game_card($game); } ?></div></section>
    <?php endif; ?>
  </main>
  <footer class="site-footer"><a class="footer-brand" href="01-home.php"><span class="brand-mark">G</span>GameSpark</a><p>Voor alle games die nog op je wachten.</p><nav aria-label="Footer navigatie"><a href="03-over-ons.php">Over ons</a><a href="08-contact.php">Contact</a><a href="02-games.php">Alle games</a></nav><span class="footer-copy">© <?= date('Y') ?> GameSpark</span></footer>
</body>
</html>
