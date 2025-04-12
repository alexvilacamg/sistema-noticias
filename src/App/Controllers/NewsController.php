<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Controllers\NewsController.php

namespace App\Controllers;

use App\Models\Scraper;
use App\Views\Helpers\ViewHelper;

class NewsController {
    public function index() {
        // Cria variáveis para a view
        $scraper = new Scraper();
        $news = $scraper->getAllPoliticalNews();
        $helper = new ViewHelper();
        
        // Inclui a view
        require_once __DIR__ . '/../Views/index.php';
    }
}
?>
