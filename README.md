# GameSpark

GameSpark is een gedeelde PHP/SQLite-gamecatalogus voor Liam en het team. Bezoekers kunnen games bekijken en toevoegen. Alleen Liam kan bestaande games bewerken of verwijderen.

## Adminlogin

De gebruikersnaam is `liam`. Stel `GAMESPARK_ADMIN_PASSWORD` in als omgevingsvariabele op de server, met een uniek wachtwoord van minimaal 12 tekens. Zet het wachtwoord niet in de broncode.

Voor de lokale PHP-server in PowerShell: stel de variabele in voordat je de server start:

```powershell
$env:GAMESPARK_ADMIN_PASSWORD = "LiamGameTester123"
php -S 127.0.0.1:8000
```

Herstart de PHP-server als je het wachtwoord wijzigt. Stel bij hosting dezelfde omgevingsvariabele in en gebruik HTTPS.

Bij Laravel Herd wordt de omgevingsvariabele niet door PHP-FPM overgenomen. Zet daarom een lokaal `data/local-config.php`-bestand in de projectmap (dit bestand staat in `.gitignore` en wordt niet meegenomen in git):

```php
<?php

return [
    'GAMESPARK_ADMIN_PASSWORD' => 'LiamGameTester123',
];
```

Het bestand wordt alleen gebruikt als de omgevingsvariabele niet is ingesteld. Wijzig het wachtwoord naar wens; de login is direct actief zonder Herd te herstarten. Gebruik een PHP-bestand zodat nginx het wachtwoord niet als platte tekst kan serveren.
