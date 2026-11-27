<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'Gebruik een POST-verzoek om een afbeelding te uploaden.']);
}

if (!isset($_FILES['cover_image']) || !is_array($_FILES['cover_image'])) {
    respond(400, ['error' => 'Geen afbeelding ontvangen.']);
}

$upload = $_FILES['cover_image'];
if ($upload['error'] !== UPLOAD_ERR_OK) {
    respond(400, ['error' => 'De afbeelding kon niet worden geüpload.']);
}

if ($upload['size'] > 3 * 1024 * 1024) {
    respond(413, ['error' => 'De afbeelding mag maximaal 3 MB zijn.']);
}

$extensionsByMime = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
];
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
if (!is_string($mime) || !isset($extensionsByMime[$mime])) {
    respond(415, ['error' => 'Kies een JPG-, PNG-, WebP- of GIF-afbeelding.']);
}

$directory = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'covers';
if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
    respond(500, ['error' => 'De map voor afbeeldingen kon niet worden aangemaakt.']);
}

$filename = bin2hex(random_bytes(16)) . '.' . $extensionsByMime[$mime];
$destination = $directory . DIRECTORY_SEPARATOR . $filename;
if (!move_uploaded_file($upload['tmp_name'], $destination)) {
    respond(500, ['error' => 'De afbeelding kon niet worden opgeslagen.']);
}

respond(201, ['path' => 'images/covers/' . $filename]);
