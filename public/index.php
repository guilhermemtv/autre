<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Bootstrap;

$bootstrap = new Bootstrap();
$router = new Router();

require __DIR__ . '/../routes/web.php';

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
