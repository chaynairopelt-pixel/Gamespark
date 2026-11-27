<header class="site-header">
  <a class="brand" href="01-home.php" aria-label="GameSpark home"><span class="brand-mark">G</span><span>GameSpark<small>PLAY YOUR WAY</small></span></a>
  <nav class="site-nav" aria-label="Hoofdnavigatie">
    <a href="01-home.php"<?= $activePage === 'home' ? ' aria-current="page"' : '' ?>>Home</a>
    <a href="02-games.php"<?= $activePage === 'games' ? ' aria-current="page"' : '' ?>>Games</a>
    <a href="03-over-ons.php"<?= $activePage === 'about' ? ' aria-current="page"' : '' ?>>Over ons</a>
  </nav>
  <div class="header-actions"><a class="header-collection" href="05-overzicht-games.php">Mijn collectie</a><a class="header-login" href="04-login.php" aria-label="Inloggen">↗</a><button class="menu-toggle" type="button" aria-expanded="false" aria-label="Menu openen"><span></span><span></span></button></div>
</header>
