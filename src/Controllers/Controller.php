<?php
namespace App\Controllers;

class Controller
{
    protected function view(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $template . '.php';
        return ob_get_clean();
    }
}
