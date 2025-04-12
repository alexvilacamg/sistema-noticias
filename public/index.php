<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\public\index.php

// Carregar o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad(); // Não falha se .env não existir

// Usa o namespace correto para o controller
use App\Controllers\NewsController;

// Instancia o controller
$controller = new NewsController();
$controller->index();
?>
