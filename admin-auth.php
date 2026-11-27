<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Deze aanvraagmethode is niet toegestaan.');
}

gamespark_session();
if (!csrf_token_is_valid($_POST['_csrf'] ?? null)) {
    http_response_code(403);
    exit('De beveiligingscontrole is verlopen. Vernieuw de pagina en probeer het opnieuw.');
}

$action = $_POST['action'] ?? '';
if ($action === 'login') {
    if (!admin_password_is_configured()) {
        header('Location: 04-login.php?error=setup', true, 303);
        exit;
    }
    if (!admin_credentials_are_valid($_POST['username'] ?? null, $_POST['password'] ?? null)) {
        header('Location: 04-login.php?error=credentials', true, 303);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['gamespark_admin'] = true;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header('Location: 05-overzicht-games.php', true, 303);
    exit;
}

if ($action === 'logout') {
    unset($_SESSION['gamespark_admin']);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    session_regenerate_id(true);
    header('Location: 05-overzicht-games.php', true, 303);
    exit;
}

http_response_code(400);
exit('Onbekende actie.');
