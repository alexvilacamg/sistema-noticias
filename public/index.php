<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\public\index.php

// Carregar o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad(); // Não falha se .env não existir

// Roteamento básico
$path = $_SERVER['REQUEST_URI'];
// Remover query string se existir
if (($pos = strpos($path, '?')) !== false) {
    $path = substr($path, 0, $pos);
}
$path = rtrim($path, '/');

// Roteamento simples
switch ($path) {
    case '/admin':
        // Usa o novo controlador administrativo
        $controller = new App\Controllers\AdminController();
        $controller->index();
        break;
        
    case '':
    case '/':
    default:
        // Usa o controlador de notícias normal
        $controller = new App\Controllers\NewsController();
        $controller->index();
        break;
}
?>
