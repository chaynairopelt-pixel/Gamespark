<?php
declare(strict_types=1);

function normalize_game_title(string $title): string
{
    $title = preg_replace('/\s+/u', ' ', trim($title));
    if ($title === null) {
        throw new RuntimeException('De gametitel bevat ongeldige tekst.');
    }
    return mb_strtolower($title, 'UTF-8');
}

function database(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $configuredPath = getenv('GAMESPARK_DB_PATH');
    $path = $configuredPath !== false && $configuredPath !== ''
        ? $configuredPath
        : __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'gamespark.sqlite';
    $directory = dirname($path);

    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('De map voor de GameSpark-database kon niet worden aangemaakt.');
    }

    $connection = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $connection->exec('PRAGMA foreign_keys = ON');
    $connection->exec('PRAGMA busy_timeout = 5000');
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            normalized_title TEXT NOT NULL,
            image TEXT,
            platform TEXT NOT NULL,
            genre TEXT NOT NULL,
            release_year INTEGER NOT NULL CHECK (release_year BETWEEN 1950 AND 2100),
            status TEXT NOT NULL CHECK (status IN (\'Wishlist\', \'In bezit\', \'Speelt nu\', \'Uitgespeeld\')),
            rating REAL NOT NULL CHECK (rating BETWEEN 1 AND 10),
            description TEXT NOT NULL DEFAULT \'\',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
    $connection->exec(
        'CREATE UNIQUE INDEX IF NOT EXISTS games_title_year_unique
         ON games (normalized_title, release_year)'
    );
    $connection->exec('CREATE TABLE IF NOT EXISTS app_meta (key TEXT PRIMARY KEY, value TEXT NOT NULL)');

    $seeded = $connection->query("SELECT value FROM app_meta WHERE key = 'catalog_seeded'")->fetchColumn();
    if ($seeded === false) {
        $games = [
            ['Red Dead Redemption 2', 'PlayStation', 'Avontuur', 2018, 'Uitgespeeld', 9.4, 'Een epische reis door het Wilde Westen.'],
            ['God of War', 'PlayStation', 'Actie', 2018, 'Uitgespeeld', 9.6, 'Een avontuurlijke reis door de Noorse mythologie.'],
            ['Minecraft', 'PC', 'Sandbox', 2011, 'Speelt nu', 9.1, 'Bouw, ontdek en maak je eigen wereld.'],
            ['Grand Theft Auto VI', 'PlayStation 5', 'Actie', 2026, 'Wishlist', 9.7, 'Een nieuw avontuur in Vice City.'],
            ['The Witcher 3', 'PC', 'RPG', 2015, 'Uitgespeeld', 9.4, 'Een uitgestrekte fantasywereld vol verhalen.'],
            ['Elden Ring', 'PlayStation 5', 'RPG', 2022, 'In bezit', 9.5, 'Verken de mysterieuze Lands Between.'],
            ['Hollow Knight: Silksong', 'Nintendo Switch', 'Platformer', 2025, 'Wishlist', 9.2, 'Een sfeervol avontuur door een nieuwe wereld.'],
            ['Stardew Valley', 'PC', 'Simulatie', 2016, 'In bezit', 9.3, 'Begin een nieuw leven op je eigen boerderij.'],
            ['Ghost of Yōtei', 'PlayStation 5', 'Avontuur', 2025, 'Wishlist', 9.0, 'Een filmisch avontuur in het noorden van Japan.'],
        ];
        $connection->beginTransaction();
        try {
            $insert = $connection->prepare(
                'INSERT INTO games (title, normalized_title, platform, genre, release_year, status, rating, description)
                 VALUES (:title, :normalized_title, :platform, :genre, :release_year, :status, :rating, :description)'
            );
            foreach ($games as [$title, $platform, $genre, $year, $status, $rating, $description]) {
                $insert->execute([
                    'title' => $title,
                    'normalized_title' => normalize_game_title($title),
                    'platform' => $platform,
                    'genre' => $genre,
                    'release_year' => $year,
                    'status' => $status,
                    'rating' => $rating,
                    'description' => $description,
                ]);
            }
            $connection->exec("INSERT INTO app_meta (key, value) VALUES ('catalog_seeded', '1')");
            $connection->commit();
        } catch (Throwable $error) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $error;
        }
    }

    return $connection;
}
