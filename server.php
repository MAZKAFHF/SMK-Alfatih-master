<?php

use Dotenv\Dotenv;

$basePath = __DIR__;
$publicPath = $basePath.'/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if ($uri !== '/' && is_file($publicPath.$uri)) {
    return false;
}

require_once $basePath.'/vendor/autoload.php';

// XAMPP installations with variables_order=GPCS do not expose an E section
// to Laravel's reloadable development server. Load the project environment
// mutably so empty child-process placeholders cannot hide the real .env values.
Dotenv::createMutable($basePath)->safeLoad();

require_once $publicPath.'/index.php';
