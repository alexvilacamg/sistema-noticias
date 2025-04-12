<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Controllers\AdminController.php

namespace App\Controllers;

use App\Factories\RepositoryFactory;

class AdminController {
    public function index() {
        // Incluir o arquivo de configuração para definir as constantes
        require_once __DIR__ . '/../../../config/config.php';
        
        // Carregar todas as notícias para estatísticas
        $repository = RepositoryFactory::createNewsRepository();
        $news = $repository->getAll();
        
        // Incluir a view de administração
        require_once __DIR__ . '/../Views/admin.php';
    }
}