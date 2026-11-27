<?php
declare(strict_types=1);

require __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function request_data(): array
{
    try {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        respond(400, ['error' => 'De aanvraag bevat geen geldige JSON.']);
    }
    if (!is_array($data)) {
        respond(400, ['error' => 'De aanvraag bevat ongeldige gegevens.']);
    }
    return $data;
}

function validate_game(array $data): array
{
    $title = is_string($data['title'] ?? null) ? trim($data['title']) : '';
    $platform = is_string($data['platform'] ?? null) ? trim($data['platform']) : '';
    $genre = is_string($data['genre'] ?? null) ? trim($data['genre']) : '';
    $status = is_string($data['status'] ?? null) ? $data['status'] : '';
    $year = filter_var($data['release_year'] ?? null, FILTER_VALIDATE_INT);
    $ratingInput = $data['rating'] ?? null;
    $rating = $ratingInput === null || $ratingInput === '' ? null : filter_var($ratingInput, FILTER_VALIDATE_FLOAT);
    $image = $data['image'] ?? null;
    $description = is_string($data['description'] ?? null) ? trim($data['description']) : '';
    $allowedStatuses = ['Wishlist', 'In bezit', 'Speelt nu', 'Uitgespeeld'];

    if ($title === '' || mb_strlen($title, 'UTF-8') > 160) {
        respond(422, ['error' => 'Vul een gametitel van maximaal 160 tekens in.']);
    }
    if ($platform === '' || mb_strlen($platform, 'UTF-8') > 80) {
        respond(422, ['error' => 'Kies een platform van maximaal 80 tekens.']);
    }
    if ($genre === '' || mb_strlen($genre, 'UTF-8') > 80) {
        respond(422, ['error' => 'Kies een genre van maximaal 80 tekens.']);
    }
    if ($year === false || $year < 1950 || $year > 2100) {
        respond(422, ['error' => 'Vul een geldig releasejaar tussen 1950 en 2100 in.']);
    }
    if (!in_array($status, $allowedStatuses, true)) {
        respond(422, ['error' => 'Kies een geldige gamestatus.']);
    }
    if ($ratingInput === null || $ratingInput === '' || $rating === false || $rating < 1 || $rating > 10) {
        respond(422, ['error' => 'De beoordeling moet tussen 1 en 10 liggen.']);
    }
    if ($image !== null && (!is_string($image) || !preg_match('/\Aimages\/covers\/[a-f0-9]{32}\.(?:jpg|png|webp|gif)\z/i', $image))) {
        respond(422, ['error' => 'De omslagafbeelding is ongeldig. Upload de afbeelding opnieuw.']);
    }
    if (mb_strlen($description, 'UTF-8') > 500) {
        respond(422, ['error' => 'De beschrijving mag maximaal 500 tekens bevatten.']);
    }

    return [
        'title' => $title,
        'normalized_title' => normalize_game_title($title),
        'platform' => $platform,
        'genre' => $genre,
        'release_year' => $year,
        'status' => $status,
        'rating' => $rating,
        'image' => $image,
        'description' => $description,
    ];
}

try {
    $db = database();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'PUT' || $method === 'DELETE') {
        if (!is_admin_logged_in()) {
            respond(401, ['error' => 'Log in als Liam om games te bewerken of te verwijderen.']);
        }
        if (!csrf_token_is_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            respond(403, ['error' => 'De beveiligingscontrole is verlopen. Vernieuw de pagina en probeer het opnieuw.']);
        }
    }

    if ($method === 'GET') {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (isset($_GET['id'])) {
            if ($id === false || $id < 1) {
                respond(400, ['error' => 'Ongeldig game-ID.']);
            }
            $statement = $db->prepare('SELECT * FROM games WHERE id = :id');
            $statement->execute(['id' => $id]);
            $game = $statement->fetch();
            if ($game === false) {
                respond(404, ['error' => 'Deze game is niet gevonden.']);
            }
            respond(200, ['game' => $game]);
        }
        $games = $db->query('SELECT * FROM games ORDER BY title COLLATE NOCASE, release_year')->fetchAll();
        respond(200, ['games' => $games]);
    }

    if ($method === 'POST') {
        $game = validate_game(request_data());
        $statement = $db->prepare(
            'INSERT INTO games (title, normalized_title, image, platform, genre, release_year, status, rating, description)
             VALUES (:title, :normalized_title, :image, :platform, :genre, :release_year, :status, :rating, :description)'
        );
        $statement->execute($game);
        $game['id'] = (int) $db->lastInsertId();
        respond(201, ['game' => $game]);
    }

    if ($method === 'PUT') {
        $data = request_data();
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            respond(400, ['error' => 'Ongeldig game-ID.']);
        }
        $game = validate_game($data);
        $statement = $db->prepare(
            'UPDATE games
             SET title = :title, normalized_title = :normalized_title, image = :image, platform = :platform,
                 genre = :genre, release_year = :release_year, status = :status, rating = :rating,
                 description = :description, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $game['id'] = $id;
        $statement->execute($game);
        if ($statement->rowCount() === 0) {
            $exists = $db->prepare('SELECT 1 FROM games WHERE id = :id');
            $exists->execute(['id' => $id]);
            if (!$exists->fetchColumn()) {
                respond(404, ['error' => 'Deze game is niet gevonden.']);
            }
        }
        respond(200, ['game' => $game]);
    }

    if ($method === 'DELETE') {
        $data = request_data();
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            respond(400, ['error' => 'Ongeldig game-ID.']);
        }
        $statement = $db->prepare('DELETE FROM games WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($statement->rowCount() === 0) {
            respond(404, ['error' => 'Deze game is niet gevonden.']);
        }
        respond(200, ['deleted' => true]);
    }

    header('Allow: GET, POST, PUT, DELETE');
    respond(405, ['error' => 'Deze HTTP-methode is niet toegestaan.']);
} catch (PDOException $error) {
    if (($error->errorInfo[1] ?? null) === 19) {
        respond(409, ['error' => 'Deze game met hetzelfde releasejaar staat al in de bibliotheek.']);
    }
    error_log('GameSpark databasefout: ' . $error->getMessage());
    respond(500, ['error' => 'De databasebewerking is mislukt. Probeer het later opnieuw.']);
} catch (Throwable $error) {
    error_log('GameSpark API-fout: ' . $error->getMessage());
    respond(500, ['error' => 'De aanvraag kon niet worden verwerkt. Probeer het later opnieuw.']);
}
