<?php
declare(strict_types=1);

require __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

$isAdmin = is_admin_logged_in();
if (($page ?? '') === 'edit' && !$isAdmin) {
    header('Location: 04-login.php');
    exit;
}

$db = database();
$catalog = $db->query('SELECT * FROM games ORDER BY title COLLATE NOCASE, release_year')->fetchAll();
$gameStatuses = ['Wishlist', 'In bezit', 'Speelt nu', 'Uitgespeeld'];

function game_tone(array $game): string
{
    $tones = ['sunset', 'ice', 'forest', 'neon', 'violet', 'gold', 'pink', 'meadow', 'rose'];
    return $tones[((int) $game['id'] - 1) % count($tones)];
}

function status_label(string $status): string
{
    return [
        'Wishlist' => 'Wil ik spelen',
        'Speelt nu' => 'Speel ik nu',
    ][$status] ?? $status;
}

$pageNames = [
    'home' => ['GameSpark — Ontdek je volgende avontuur', 'Home'],
    'games' => ['Ontdek games — GameSpark', 'Games'],
    'about' => ['Over GameSpark', 'Over ons'],
    'login' => ['Teamtoegang — GameSpark', 'Teamtoegang'],
    'collection' => ['Gameoverzicht — GameSpark', 'Gameoverzicht'],
    'create' => ['Game toevoegen — GameSpark', 'Game toevoegen'],
    'edit' => ['Game bewerken — GameSpark', 'Game bewerken'],
    'contact' => ['Contact — GameSpark', 'Contact'],
    'detail' => ['Game details — GameSpark', 'Gamedetails'],
];
$page = isset($pageNames[$page ?? '']) ? $page : 'home';
$title = $pageNames[$page][0];
$activePage = $page === 'detail' ? 'games' : ($page === 'create' || $page === 'edit' ? 'collection' : $page);
$selectedGame = null;
$editGame = null;
if ($page === 'detail' || $page === 'edit') {
    $requestedId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if ($requestedId !== false && $requestedId !== null) {
        $statement = $db->prepare('SELECT * FROM games WHERE id = :id');
        $statement->execute(['id' => $requestedId]);
        $requestedGame = $statement->fetch();
        if ($page === 'detail') {
            $selectedGame = $requestedGame ?: null;
        } else {
            $editGame = $requestedGame ?: null;
        }
    } elseif ($page === 'detail' && $catalog !== []) {
        $selectedGame = $catalog[0];
    }
}

$platforms = $db->query('SELECT DISTINCT platform FROM games ORDER BY platform COLLATE NOCASE')->fetchAll(PDO::FETCH_COLUMN);
$genres = $db->query('SELECT DISTINCT genre FROM games ORDER BY genre COLLATE NOCASE')->fetchAll(PDO::FETCH_COLUMN);
$catalogResults = $catalog;
if ($page === 'games' || $page === 'collection') {
    $where = [];
    $parameters = [];
    foreach (['platform', 'genre', 'status'] as $filter) {
        $value = trim((string) ($_GET[$filter] ?? ''));
        if ($value !== '') {
            $where[] = "$filter = :$filter";
            $parameters[$filter] = $value;
        }
    }
    $minimumRating = filter_var($_GET['rating'] ?? null, FILTER_VALIDATE_INT);
    if ($minimumRating !== false && $minimumRating !== null && $minimumRating >= 1 && $minimumRating <= 10) {
        $where[] = 'rating >= :rating';
        $parameters['rating'] = $minimumRating;
    }
    $sortOptions = [
        'title' => 'title COLLATE NOCASE ASC, release_year ASC',
        'year' => 'release_year DESC, title COLLATE NOCASE ASC',
        'rating' => 'rating DESC, title COLLATE NOCASE ASC',
    ];
    $defaultSort = $page === 'collection' ? 'title' : 'rating';
    $sort = (string) ($_GET['sort'] ?? $defaultSort);
    $orderBy = $sortOptions[$sort] ?? $sortOptions['rating'];
    $query = 'SELECT * FROM games' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $orderBy;
    $statement = $db->prepare($query);
    $statement->execute($parameters);
    $catalogResults = $statement->fetchAll();
}
$popularGames = $catalog;
usort($popularGames, static fn(array $a, array $b): int => (float) ($b['rating'] ?? 0) <=> (float) ($a['rating'] ?? 0));
$featuredGame = $popularGames[0] ?? null;

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function game_card(array $game, string $extraClass = ''): void
{
    $tone = game_tone($game);
    ?>
    <article class="game-card <?= e($extraClass) ?>">
      <a class="game-cover cover-<?= e($tone) ?>" href="09-detail-game.php?id=<?= e($game['id']) ?>" aria-label="Bekijk <?= e($game['title']) ?>">
        <span class="cover-orbit" aria-hidden="true"></span>
        <?php if (!empty($game['image'])): ?><img class="game-thumbnail" src="<?= e($game['image']) ?>" alt="" loading="lazy"><?php endif; ?>
        <span class="cover-label"><?= e($game['genre']) ?> <span>·</span> <?= e($game['release_year']) ?></span>
        <span class="cover-title"><?= e($game['title']) ?></span>
        <span class="cover-arrow" aria-hidden="true">↗</span>
      </a>
      <div class="game-card-content">
        <div class="game-name-line">
          <a href="09-detail-game.php?id=<?= e($game['id']) ?>"><?= e($game['title']) ?></a>
          <span class="score"><?= $game['rating'] !== null ? '★ ' . e($game['rating']) : 'Nog geen rating' ?></span>
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
  <meta name="description" content="GameSpark — het gedeelde game- en reviewoverzicht voor Liam en zijn team.">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e($title) ?></title>
  <link rel="stylesheet" href="css/styles.css">
  <script src="js/app.js" defer></script>
</head>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page-shell" data-page="<?= e($page) ?>">
    <?php if ($page === 'home'): ?>
      <section class="hero">
        <div class="hero-copy">
          <span class="eyebrow"><span class="pulse-dot"></span> HET GAMEOVERZICHT VOOR LIAM &amp; TEAM</span>
          <h1>Games gespeeld.<br><span>Reviews gedeeld.</span></h1>
          <p>Een centrale plek voor Liams game-ervaringen, speelplanning en aanbevelingen. Zo weet het team wat al gespeeld is en wat er nog op de wishlist staat.</p>
          <div class="hero-actions"><a class="button button-primary" href="02-games.php">Bekijk alle games <span>↗</span></a><a class="button button-quiet" href="06-aanmaken.php">＋ Game toevoegen</a></div>
          <div class="hero-proof"><span class="proof-stars">★★★★★</span><span>Ratings als inspiratie voor reviews</span><span class="proof-divider"></span><span><?= count($catalog) ?> games bijgehouden</span></div>
        </div>
        <?php if ($featuredGame !== null): ?><a class="hero-feature cover-<?= e(game_tone($featuredGame)) ?>" href="09-detail-game.php?id=<?= e($featuredGame['id']) ?>">
          <?php if (!empty($featuredGame['image'])): ?><img class="game-thumbnail" src="<?= e($featuredGame['image']) ?>" alt="" loading="lazy"><?php endif; ?>
          <span class="hero-feature-glow"></span><span class="feature-topline"><span class="live-tag">★ HOOGSTE RATING</span><span><?= count($catalog) ?> GAMES</span></span>
          <span class="feature-art-symbol" aria-hidden="true">✦</span>
          <span class="feature-bottom"><span class="feature-category"><?= e($featuredGame['genre']) ?> · <?= e($featuredGame['release_year']) ?></span><strong><?= e($featuredGame['title']) ?></strong><span class="feature-rating"><?= $featuredGame['rating'] !== null ? '★ ' . e($featuredGame['rating']) : 'Nog geen rating' ?> <small>/ 10</small></span><span class="feature-link">Bekijk game <b>↗</b></span></span>
        </a><?php else: ?><div class="hero-feature"><span class="feature-bottom"><strong>De gamebibliotheek is nog leeg.</strong><a class="button button-primary" href="06-aanmaken.php">Voeg de eerste game toe</a></span></div><?php endif; ?>
        <div class="hero-orb orb-one"></div><div class="hero-orb orb-two"></div>
      </section>
      <section class="home-section">
        <div class="section-heading"><div><span class="eyebrow">RATINGS ALS REVIEWINSPIRATIE</span><h2>Hoogst beoordeeld</h2></div><a class="text-link" href="02-games.php">Bekijk alle games <span>→</span></a></div>
        <div class="game-grid home-grid"><?php foreach (array_slice($popularGames, 0, 4) as $game) { game_card($game); } ?></div>
      </section>
      <section class="collection-promo"><div class="promo-icon">✳</div><div><span class="eyebrow">EEN GEDEELD OVERZICHT VOOR HET TEAM</span><h2>Van speelplanning tot review.</h2><p>Iedereen bekijkt dezelfde serverbibliotheek met games, platforms, statussen en ratings.</p></div><a class="button button-primary" href="05-overzicht-games.php">Open gameoverzicht <span>→</span></a></section>

    <?php elseif ($page === 'games'): ?>
      <section class="page-intro"><span class="eyebrow">GAMES &amp; REVIEWS VAN LIAM</span><h1>De gedeelde <span>gamebibliotheek.</span></h1><p>Bekijk welke games al zijn gespeeld, wat er op de wishlist staat en welke ratings het team als reviewinspiratie kan gebruiken.</p></section>
      <section class="catalog-layout">
        <aside class="genre-panel"><h2>Genres</h2><p>Kies een wereld om in te duiken.</p><div class="genre-list">
          <a class="genre-filter<?= empty($_GET['genre']) ? ' is-selected' : '' ?>" href="02-games.php">Alles<span>↗</span></a>
          <?php foreach ($genres as $genre): ?><a class="genre-filter<?= ($_GET['genre'] ?? '') === $genre ? ' is-selected' : '' ?>" href="?genre=<?= rawurlencode($genre) ?>"><?= e($genre) ?><span>↗</span></a><?php endforeach; ?>
        </div><div class="sidebar-note"><span>✦</span><p>De mooiste avonturen beginnen met een ontdekking.</p></div></aside>
        <div class="catalog-main"><div class="catalog-toolbar"><div><span class="eyebrow">CENTRAAL OVERZICHT</span><h2>Games <span class="results-count">(<?= count($catalogResults) ?> van <?= count($catalog) ?>)</span></h2></div></div>
          <form class="filter-bar games-filter" method="get" action="02-games.php">
            <label>Platform<select name="platform"><option value="">Alle platforms</option><?php foreach ($platforms as $platform): ?><option value="<?= e($platform) ?>"<?= ($_GET['platform'] ?? '') === $platform ? ' selected' : '' ?>><?= e($platform) ?></option><?php endforeach; ?></select></label>
            <label>Genre<select name="genre"><option value="">Alle genres</option><?php foreach ($genres as $genre): ?><option value="<?= e($genre) ?>"<?= ($_GET['genre'] ?? '') === $genre ? ' selected' : '' ?>><?= e($genre) ?></option><?php endforeach; ?></select></label>
            <label>Status<select name="status"><option value="">Alle statussen</option><?php foreach ($gameStatuses as $status): ?><option value="<?= e($status) ?>"<?= ($_GET['status'] ?? '') === $status ? ' selected' : '' ?>><?= e(status_label($status)) ?></option><?php endforeach; ?></select></label>
            <label>Min. beoordeling<select name="rating"><option value="">Alle scores</option><?php for ($rating = 9; $rating >= 5; $rating--): ?><option value="<?= $rating ?>"<?= (string) ($_GET['rating'] ?? '') === (string) $rating ? ' selected' : '' ?>><?= $rating ?>+ / 10</option><?php endfor; ?></select></label>
            <label class="sort-control">Sorteren<select name="sort"><option value="rating"<?= ($_GET['sort'] ?? 'rating') === 'rating' ? ' selected' : '' ?>>Rating</option><option value="title"<?= ($_GET['sort'] ?? '') === 'title' ? ' selected' : '' ?>>Naam A–Z</option><option value="year"<?= ($_GET['sort'] ?? '') === 'year' ? ' selected' : '' ?>>Nieuwste eerst</option></select></label>
            <button class="button button-primary" type="submit">Filter games</button>
          </form>
          <div class="game-grid catalog-grid"><?php foreach ($catalogResults as $game) { game_card($game); } ?></div>
          <?php if ($catalogResults === []): ?><p class="no-results">Geen games gevonden met deze filters. Pas je selectie aan.</p><?php endif; ?>
        </div>
      </section>

    <?php elseif ($page === 'about'): ?>
      <section class="about-hero"><div class="about-art"><span class="about-art-ring"></span><span class="about-art-star">✳</span><span class="about-art-caption">GAME<br>REVIEWS<br>TEAM</span><span class="about-art-coord">GAMESPARK · SHARED DATABASE</span></div><div class="about-copy"><span class="eyebrow">OVER GAMESPARK</span><h1>Een helder beeld<br><span>voor Liam &amp; team.</span></h1><p>Liam test games, schrijft korte reviews en deelt zijn ervaringen met GameSpark. Met dit centrale overzicht kan het team zien welke titels hij al heeft gespeeld en wat hij nog wil proberen.</p><p>Platform, genre, speelstatus en rating staan samen op één plek. Collega’s gebruiken die informatie als inspiratie voor reviews en aanbevelingen.</p><a class="button button-primary" href="02-games.php">Bekijk het overzicht <span>↗</span></a></div></section>
      <section class="values-grid"><article><span class="value-icon">◈</span><h2>Speelvoortgang</h2><p>Zie in één oogopslag wat Liam al heeft gespeeld, bezit of nog op de wishlist heeft.</p></article><article><span class="value-icon">✳</span><h2>Reviewinspiratie</h2><p>Korte beschrijvingen en ratings helpen collega’s bij reviews en gametips.</p></article><article><span class="value-icon">⌁</span><h2>Eén gedeelde lijst</h2><p>Iedereen bekijkt dezelfde games in de centrale database, niet een privécollectie.</p></article></section>

    <?php elseif ($page === 'login'): ?>
      <section class="auth-layout">
        <div class="auth-aside cover-violet"><span class="eyebrow">LIAM · BEHEERDER</span><span class="auth-aside-mark">✳</span><h2>Alle games.<br>Eén overzicht.</h2><p>Iedereen kan de gedeelde catalogus bekijken en games toevoegen. Alleen Liam kan bestaande games aanpassen of verwijderen.</p><span class="auth-aside-small">GAMESPARK · CENTRALE DATABASE</span></div>
        <div class="auth-panel">
          <span class="eyebrow">BEHEERDERSTOEGANG</span>
          <?php if ($isAdmin): ?>
            <h1>Welkom, Liam.</h1><p>Je bent ingelogd. Je kunt games nu bewerken en verwijderen.</p>
            <div class="team-access-card"><a class="button button-primary" href="05-overzicht-games.php">Naar gameoverzicht <span>→</span></a></div>
          <?php else: ?>
            <h1>Admin <span>login.</span></h1><p>Log in als Liam om games in de gedeelde bibliotheek te beheren.</p>
            <?php if (($_GET['error'] ?? '') === 'credentials'): ?><p class="form-feedback is-error" role="alert">Gebruikersnaam of wachtwoord is onjuist.</p><?php endif; ?>
            <?php if (!admin_password_is_configured()): ?>
              <p class="form-feedback is-error" role="alert">De beheerlogin is nog niet ingesteld. Stel <code>GAMESPARK_ADMIN_PASSWORD</code> in op de server (minimaal 12 tekens). Zie README voor de startinstructies.</p>
            <?php else: ?>
              <form class="admin-login-form" action="admin-auth.php" method="post">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <label for="admin-username">Gebruikersnaam</label><input id="admin-username" name="username" value="liam" autocomplete="username" required>
                <label for="admin-password">Wachtwoord</label><input id="admin-password" name="password" type="password" autocomplete="current-password" minlength="12" required>
                <button class="button button-primary" type="submit">Inloggen als Liam <span>→</span></button>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </section>

    <?php elseif ($page === 'collection'): ?>
      <section class="page-intro compact-intro"><span class="eyebrow">GEDEELDE GAMEBIBLIOTHEEK</span><h1>Game<span>overzicht.</span></h1><p>De games, speelplanning en reviewratings die Liam met het GameSpark-team deelt.</p></section>
      <div class="collection-toolbar"><div class="collection-stat"><strong><?= count($catalogResults) ?></strong><span>van <?= count($catalog) ?> games</span></div><a class="button button-primary" href="06-aanmaken.php"><span>＋</span> Game toevoegen</a></div>
      <form class="filter-bar games-filter" method="get" action="05-overzicht-games.php" aria-label="Gameoverzicht filteren">
        <label>Platform<select name="platform"><option value="">Alle platforms</option><?php foreach ($platforms as $platform): ?><option value="<?= e($platform) ?>"<?= ($_GET['platform'] ?? '') === $platform ? ' selected' : '' ?>><?= e($platform) ?></option><?php endforeach; ?></select></label>
        <label>Genre<select name="genre"><option value="">Alle genres</option><?php foreach ($genres as $genre): ?><option value="<?= e($genre) ?>"<?= ($_GET['genre'] ?? '') === $genre ? ' selected' : '' ?>><?= e($genre) ?></option><?php endforeach; ?></select></label>
        <label>Status<select name="status"><option value="">Alle statussen</option><?php foreach ($gameStatuses as $status): ?><option value="<?= e($status) ?>"<?= ($_GET['status'] ?? '') === $status ? ' selected' : '' ?>><?= e(status_label($status)) ?></option><?php endforeach; ?></select></label>
        <label>Beoordeling<select name="rating"><option value="">Alle scores</option><?php for ($rating = 9; $rating >= 5; $rating--): ?><option value="<?= $rating ?>"<?= (string) ($_GET['rating'] ?? '') === (string) $rating ? ' selected' : '' ?>><?= $rating ?>+ / 10</option><?php endfor; ?></select></label>
        <label class="sort-control">Sorteren<select name="sort"><option value="title"<?= ($_GET['sort'] ?? 'title') === 'title' ? ' selected' : '' ?>>Naam A–Z</option><option value="year"<?= ($_GET['sort'] ?? '') === 'year' ? ' selected' : '' ?>>Releasejaar</option><option value="rating"<?= ($_GET['sort'] ?? '') === 'rating' ? ' selected' : '' ?>>Rating</option></select></label>
        <button class="button button-primary" type="submit">Filter games</button>
      </form>
      <div class="table-wrap"><table class="games-table"><thead><tr><th>Afbeelding</th><th>Game</th><th>Platform</th><th>Status</th><th>Rating</th><th>Acties</th></tr></thead><tbody>
      <?php foreach ($catalogResults as $game): ?><tr>
        <td><?php if (!empty($game['image'])): ?><img class="table-cover" src="<?= e($game['image']) ?>" alt="Omslag van <?= e($game['title']) ?>"><?php else: ?><span class="table-cover-placeholder cover-<?= e(game_tone($game)) ?>" aria-hidden="true">✦</span><?php endif; ?></td>
        <td><a class="table-game-title" href="09-detail-game.php?id=<?= e($game['id']) ?>"><?= e($game['title']) ?></a><span class="table-game-meta"><?= e($game['genre']) ?> · <?= e($game['release_year']) ?></span></td>
        <td><?= e($game['platform']) ?></td><td><span class="status-pill status-<?= e(strtolower(str_replace(' ', '-', $game['status']))) ?>"><?= e(status_label($game['status'])) ?></span></td><td><?= $game['rating'] !== null ? '★ ' . e($game['rating']) . ' / 10' : '—' ?></td>
        <td class="table-actions"><a href="09-detail-game.php?id=<?= e($game['id']) ?>">Bekijken</a><?php if ($isAdmin): ?><a href="07-updaten.php?id=<?= e($game['id']) ?>">Bewerken</a><button type="button" class="delete-game" data-delete-id="<?= e($game['id']) ?>" data-game-title="<?= e($game['title']) ?>">Verwijderen</button><?php endif; ?></td>
      </tr><?php endforeach; ?>
      </tbody></table></div>
      <?php if ($catalogResults === []): ?><section class="empty-collection"><span>✳</span><h2>Geen games gevonden.</h2><p>Pas de filters aan of voeg een game toe aan de bibliotheek.</p><a class="button button-primary" href="06-aanmaken.php">Game toevoegen <span>→</span></a></section><?php endif; ?>

    <?php elseif ($page === 'create' || $page === 'edit'): ?>
      <a class="back-link" href="05-overzicht-games.php">← <span>Terug naar gameoverzicht</span></a>
      <?php if ($page === 'edit' && $editGame === null): ?>
        <section class="empty-collection"><span>✳</span><h2>Game niet gevonden.</h2><p>Deze game bestaat niet (meer) in de gedeelde bibliotheek.</p><a class="button button-primary" href="05-overzicht-games.php">Terug naar overzicht <span>→</span></a></section>
      <?php else: $formGame = $editGame ?? ['id' => '', 'title' => '', 'platform' => '', 'genre' => '', 'release_year' => '', 'status' => '', 'rating' => '', 'description' => '', 'image' => null]; ?>
      <section class="form-page">
        <div class="form-page-heading"><span class="eyebrow"><?= $page === 'edit' ? 'CENTRALE BIBLIOTHEEK BIJWERKEN' : 'VOEG EEN GAME TOE VOOR HET TEAM' ?></span><h1><?= $page === 'edit' ? 'Game bewerken.' : 'Game toevoegen.' ?></h1><p><?= $page === 'edit' ? 'De bijgewerkte gegevens zijn direct zichtbaar voor het hele team.' : 'Nieuwe game-invoer wordt gedeeld met Liam en alle collega’s.' ?></p></div>
        <form class="game-form" id="game-form" enctype="multipart/form-data" novalidate data-game-id="<?= e($formGame['id']) ?>" data-current-image="<?= e($formGame['image'] ?? '') ?>">
          <div class="form-columns">
            <label>Naam van de game <span>*</span><input name="title" value="<?= e($formGame['title']) ?>" maxlength="160" placeholder="Bijv. Hades II" required></label>
            <label>Platform <span>*</span><select name="platform" required><option value="">Kies een platform</option><?php foreach (['PC', 'PlayStation', 'PlayStation 5', 'PlayStation 4', 'Xbox', 'Xbox Series X/S', 'Xbox One', 'Nintendo Switch', 'Steam Deck', 'Mobile'] as $platform): ?><option value="<?= e($platform) ?>"<?= $formGame['platform'] === $platform ? ' selected' : '' ?>><?= e($platform) ?></option><?php endforeach; ?><?php if ($formGame['platform'] !== '' && !in_array($formGame['platform'], ['PC', 'PlayStation', 'PlayStation 5', 'PlayStation 4', 'Xbox', 'Xbox Series X/S', 'Xbox One', 'Nintendo Switch', 'Steam Deck', 'Mobile'], true)): ?><option value="<?= e($formGame['platform']) ?>" selected><?= e($formGame['platform']) ?></option><?php endif; ?></select></label>
            <label>Genre <span>*</span><select name="genre" required><option value="">Kies een genre</option><?php foreach (array_unique(array_merge(['Actie', 'Avontuur', 'RPG', 'Sandbox', 'Simulatie', 'Strategie', 'Sport', 'Platformer', 'Racing', 'Puzzle'], $genres)) as $genre): ?><option value="<?= e($genre) ?>"<?= $formGame['genre'] === $genre ? ' selected' : '' ?>><?= e($genre) ?></option><?php endforeach; ?><?php if ($formGame['genre'] !== '' && !in_array($formGame['genre'], array_unique(array_merge(['Actie', 'Avontuur', 'RPG', 'Sandbox', 'Simulatie', 'Strategie', 'Sport', 'Platformer', 'Racing', 'Puzzle'], $genres)), true)): ?><option value="<?= e($formGame['genre']) ?>" selected><?= e($formGame['genre']) ?></option><?php endif; ?></select></label>
            <label>Releasejaar <span>*</span><input name="release_year" type="number" min="1950" max="2100" value="<?= e($formGame['release_year']) ?>" placeholder="2025" required></label>
            <label>Status <span>*</span><select name="status" required><option value="">Kies status</option><?php foreach ($gameStatuses as $status): ?><option value="<?= e($status) ?>"<?= $formGame['status'] === $status ? ' selected' : '' ?>><?= e(status_label($status)) ?></option><?php endforeach; ?></select></label>
            <label>Beoordeling (1–10) <span>*</span><input name="rating" type="number" min="1" max="10" step="0.1" value="<?= e($formGame['rating']) ?>" placeholder="Bijv. 8.5" required></label>
            <label class="upload-field">Omslagafbeelding <span class="optional">(optioneel, max. 3 MB)</span><input name="cover_image" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><span class="upload-help">JPG, PNG, WebP of GIF. Laat leeg om de huidige afbeelding te behouden.</span><?php if (!empty($formGame['image'])): ?><img class="form-image-preview" src="<?= e($formGame['image']) ?>" alt="Huidige omslagafbeelding"><?php endif; ?></label>
            <label class="notes-field">Korte beschrijving of review <span class="optional">(optioneel)</span><textarea name="description" rows="4" maxlength="500" placeholder="Wat heeft Liam van deze game gevonden?"><?= e($formGame['description']) ?></textarea></label>
          </div>
          <p class="form-feedback" id="game-form-feedback" role="alert" hidden></p>
          <div class="form-actions"><a class="button button-quiet" href="05-overzicht-games.php">Annuleren</a><button class="button button-primary" type="submit" id="game-save"><?= $page === 'edit' ? 'Wijzigingen opslaan' : 'Game toevoegen' ?> <span>→</span></button><button class="button button-danger" type="button" id="game-delete"<?= $page !== 'edit' ? ' hidden' : '' ?>>Verwijderen</button></div>
        </form>
      </section>
      <?php endif; ?>

    <?php elseif ($page === 'contact'): ?>
      <section class="page-intro compact-intro"><span class="eyebrow">WE HOREN GRAAG VAN JE</span><h1>Even <span>bijpraten?</span></h1><p>Een vraag, idee of game die we echt moeten proberen? Stuur ons een bericht.</p></section><section class="contact-layout"><form class="contact-form" id="contact-form"><div class="form-columns"><label>Je naam <span>*</span><input name="name" autocomplete="name" required placeholder="Hoe mogen we je noemen?"></label><label>E-mailadres <span>*</span><input name="email" type="email" autocomplete="email" required placeholder="jij@voorbeeld.nl"></label><label class="subject-field">Onderwerp <span>*</span><select name="subject" required><option value="">Kies een onderwerp</option><option>Algemene vraag</option><option>Idee of suggestie</option><option>Probleem met mijn collectie</option><option>Anders</option></select></label><label class="message-field">Je bericht <span>*</span><textarea name="message" rows="6" required placeholder="Vertel ons waar je mee zit..."></textarea></label></div><p class="form-feedback" id="contact-feedback" role="status" hidden></p><button class="button button-primary" type="submit">Verstuur bericht <span>→</span></button></form><aside class="contact-aside"><span class="eyebrow">CONTACTGEGEVENS</span><div class="contact-detail"><span>✉</span><div><small>E-MAIL</small><a href="mailto:info@gamespark.nl">info@gamespark.nl</a></div></div><div class="contact-detail"><span>◷</span><div><small>REACTIETIJD</small><p>We reageren binnen 2 werkdagen.</p></div></div><div class="contact-detail"><span>⌖</span><div><small>GAMESPARK</small><p>Bergen op Zoom, Nederland</p></div></div><div class="contact-aside-art"><span>GG</span><small>LET'S TALK GAMES</small></div></aside></section>

    <?php elseif ($page === 'detail' && $selectedGame !== null): ?>
      <a class="back-link" href="02-games.php">← <span>Terug naar gamebibliotheek</span></a>
      <section class="detail-hero">
        <div class="detail-art cover-<?= e(game_tone($selectedGame)) ?>"><span class="cover-orbit" aria-hidden="true"></span><?php if (!empty($selectedGame['image'])): ?><img class="game-thumbnail" src="<?= e($selectedGame['image']) ?>" alt=""><?php endif; ?><span class="detail-art-mark">✳</span><span class="cover-label"><?= e($selectedGame['genre']) ?> · <?= e($selectedGame['release_year']) ?></span><span class="cover-title"><?= e($selectedGame['title']) ?></span></div>
        <div class="detail-copy"><span class="eyebrow">GAME IN DE GEDEELDE BIBLIOTHEEK</span><h1><?= e($selectedGame['title']) ?></h1><div class="detail-rating"><?= $selectedGame['rating'] !== null ? '<span>★★★★★</span> ' . e($selectedGame['rating']) . '<small> / 10</small>' : '<small>Nog geen rating</small>' ?></div><p><?= e($selectedGame['description']) ?></p><dl class="detail-facts"><div><dt>Platform</dt><dd><?= e($selectedGame['platform']) ?></dd></div><div><dt>Genre</dt><dd><?= e($selectedGame['genre']) ?></dd></div><div><dt>Releasejaar</dt><dd><?= e($selectedGame['release_year']) ?></dd></div><div><dt>Speelstatus</dt><dd><?= e(status_label($selectedGame['status'])) ?></dd></div></dl><?php if ($isAdmin): ?><div class="detail-actions"><a class="button button-primary" href="07-updaten.php?id=<?= e($selectedGame['id']) ?>">Game bewerken <span>→</span></a><button class="button button-danger delete-game" type="button" data-delete-id="<?= e($selectedGame['id']) ?>" data-game-title="<?= e($selectedGame['title']) ?>">Verwijderen</button></div><p class="form-feedback" id="detail-feedback" role="status" hidden></p><?php endif; ?></div>
      </section>
      <section class="home-section related-section"><div class="section-heading"><div><span class="eyebrow">ANDERE GAMES IN DE BIBLIOTHEEK</span><h2>Meer game-ervaringen</h2></div><a class="text-link" href="02-games.php">Alle games <span>→</span></a></div><div class="game-grid home-grid"><?php foreach (array_slice(array_values(array_filter($catalog, static fn(array $game): bool => (int) $game['id'] !== (int) $selectedGame['id'])), 0, 4) as $game) { game_card($game); } ?></div></section>
    <?php elseif ($page === 'detail'): ?>
      <a class="back-link" href="02-games.php">← <span>Terug naar gamebibliotheek</span></a><section class="empty-collection"><span>✳</span><h2>Game niet gevonden.</h2><p>Deze game staat niet (meer) in de gedeelde bibliotheek.</p><a class="button button-primary" href="02-games.php">Bekijk games <span>→</span></a></section>
    <?php endif; ?>
  </main>
  <footer class="site-footer"><a class="footer-brand" href="01-home.php"><span class="brand-mark">G</span>GameSpark</a><p>Gedeeld gameoverzicht voor Liam &amp; collega's.</p><nav aria-label="Footer navigatie"><a href="03-over-ons.php">Over ons</a><a href="08-contact.php">Contact</a><a href="02-games.php">Gamebibliotheek</a></nav><span class="footer-copy">© <?= date('Y') ?> GameSpark</span></footer>
</body>
</html>
