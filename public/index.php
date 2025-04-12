<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\public\index.php

// Carrega o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Usa o namespace correto para o controller
use App\Controllers\NewsController;

// Instancia o controller
$controller = new NewsController();
$controller->index();
?>
