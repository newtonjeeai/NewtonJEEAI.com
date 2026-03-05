<?php

declare(strict_types=1);

// Load .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ── App constants ────────────────────────────────────────────
define('APP_NAME',     env('APP_NAME', 'NewtonJEE'));
define('APP_URL',      rtrim(env('APP_URL', 'https://newtonjee.com'), '/'));
define('APP_DEBUG',    env('APP_DEBUG', 'false') === 'true');
define('APP_TIMEZONE', env('APP_TIMEZONE', 'Asia/Kolkata'));

date_default_timezone_set(APP_TIMEZONE);

// ── Paths ────────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__));
define('APP_PATH',     ROOT_PATH . '/app');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('VIEWS_PATH',   APP_PATH  . '/Views');
define('PRIVATE_PATH', '/var/www/private');   // Outside web root
define('NOTEBOOK_PATH', PRIVATE_PATH . '/notebooks');

// ── Session ──────────────────────────────────────────────────
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 28800));
define('SESSION_NAME',     env('SESSION_NAME', 'nj_session'));

// ── File limits ──────────────────────────────────────────────
define('UPLOAD_MAX_BYTES',    (int) env('UPLOAD_MAX_SIZE', 52428800));
define('NOTEBOOK_MAX_BYTES',  (int) env('NOTEBOOK_MAX_SIZE', 26214400));

// ── Mentor Drive email ───────────────────────────────────────
define('MENTOR_DRIVE_EMAIL', env('MENTOR_DRIVE_EMAIL', 'mentor@newtonjee.com'));

// ── Roles ────────────────────────────────────────────────────
define('ROLE_STUDENT',     'student');
define('ROLE_MENTOR',      'mentor');
define('ROLE_ADMIN',       'admin');
define('ROLE_SUPER_ADMIN', 'super_admin');

// ── Error handling ───────────────────────────────────────────
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
    set_exception_handler(function (Throwable $e) {
        error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        include VIEWS_PATH . '/errors/500.php';
        exit;
    });
}
