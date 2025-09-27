<?php
use App\Controllers\HomeController;

$home = new HomeController();

$router->get('/', function () use ($home) {
    return $home->index();
});
