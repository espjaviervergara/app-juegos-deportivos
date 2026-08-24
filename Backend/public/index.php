<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\App;
use App\Routes;

$config = require __DIR__ . '/../config/app.php';

// Cargar .env si existe (simple)
if (file_exists(__DIR__.'/../.env')) {
    foreach (file(__DIR__.'/../.env', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line),'#')) continue;
        [$k,$v] = array_map('trim', explode('=', $line, 2) + ['','']);
        $_ENV[$k] = $v;
    }
}

$router = new Router();
Routes::register($router);

$app = new App($router, $config);
$app->run();
