<?php

$setServerlessDefault = static function (string $key, string $value): void {
    if (getenv($key) !== false || isset($_ENV[$key]) || isset($_SERVER[$key])) {
        return;
    }

    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
};

$setServerlessDefault('APP_ENV', 'production');
$setServerlessDefault('APP_DEBUG', 'false');
$setServerlessDefault('CACHE_STORE', 'array');
$setServerlessDefault('SESSION_DRIVER', 'cookie');
$setServerlessDefault('LOG_CHANNEL', 'stderr');
$setServerlessDefault('APP_CONFIG_CACHE', '/tmp/config.php');
$setServerlessDefault('APP_EVENTS_CACHE', '/tmp/events.php');
$setServerlessDefault('APP_PACKAGES_CACHE', '/tmp/packages.php');
$setServerlessDefault('APP_ROUTES_CACHE', '/tmp/routes.php');
$setServerlessDefault('APP_SERVICES_CACHE', '/tmp/services.php');
$setServerlessDefault('VIEW_COMPILED_PATH', '/tmp');

$databaseUrl = getenv('DATABASE_URL');
if (is_string($databaseUrl) && $databaseUrl !== '') {
    $setServerlessDefault('DB_URL', $databaseUrl);
}

$vercelHost = getenv('VERCEL_PROJECT_PRODUCTION_URL') ?: getenv('VERCEL_URL');
if (is_string($vercelHost) && $vercelHost !== '') {
    $setServerlessDefault('APP_URL', 'https://'.$vercelHost);
}

require __DIR__ . '/../public/index.php';
