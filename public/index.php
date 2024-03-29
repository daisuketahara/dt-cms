<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

chdir(dirname(__DIR__));

// Check if .env exist, else redirect to install script
if (!file_exists('.env')) {
    require '../install/index.php';
}

require 'vendor/autoload.php';

// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }

    $httpHostExplode = explode('.', $_SERVER['HTTP_HOST']);
    $subdomain = array_shift($httpHostExplode);
    if ($subdomain == 'www') $subdomain = '';

    if (file_exists(__DIR__.'/../.env.'.$subdomain)) $env = __DIR__.'/../.env.'.$subdomain;
    else $env = __DIR__.'/../.env';

    (new Dotenv())->load($env);
}

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = $_SERVER['APP_DEBUG'] ?? ('prod' !== $env);

if (!defined('APPLICATION_VERSION')) define('APPLICATION_VERSION', '0.0.1-dev');

if ($debug) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}

$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
