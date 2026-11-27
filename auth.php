<?php
declare(strict_types=1);

function gamespark_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('gamespark_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function gamespark_env(string $key): string|false
{
    $value = getenv($key);
    if (is_string($value) && $value !== '') {
        return $value;
    }

    // Herd's PHP-FPM does not inherit shell environment variables, so allow a
    // local, gitignored PHP config file as a fallback for development. A .php
    // file keeps the secret from being served as plain text by nginx.
    static $localConfig = null;
    if ($localConfig === null) {
        $localConfig = [];
        $path = __DIR__ . '/data/local-config.php';
        if (is_readable($path)) {
            $values = require $path;
            if (is_array($values)) {
                foreach ($values as $name => $value) {
                    if (is_string($name) && is_string($value)) {
                        $localConfig[$name] = $value;
                    }
                }
            }
        }
    }

    return $localConfig[$key] ?? false;
}

function admin_password_is_configured(): bool
{
    $password = gamespark_env('GAMESPARK_ADMIN_PASSWORD');
    return is_string($password) && strlen($password) >= 12;
}

function admin_credentials_are_valid(mixed $username, mixed $password): bool
{
    $expectedPassword = gamespark_env('GAMESPARK_ADMIN_PASSWORD');
    if (!is_string($expectedPassword) || strlen($expectedPassword) < 12 || !is_string($password)) {
        return false;
    }

    $usernameIsValid = is_string($username) && hash_equals('liam', strtolower(trim($username)));
    $passwordIsValid = hash_equals($expectedPassword, $password);
    return $usernameIsValid && $passwordIsValid;
}

function is_admin_logged_in(): bool
{
    gamespark_session();
    return ($_SESSION['gamespark_admin'] ?? false) === true;
}

function csrf_token(): string
{
    gamespark_session();
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_token_is_valid(mixed $token): bool
{
    return is_string($token) && hash_equals(csrf_token(), $token);
}
