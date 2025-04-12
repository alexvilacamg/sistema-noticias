<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Controllers\NewsController.php

namespace App\Controllers;

use App\Models\Scraper;

class NewsController {
    public function index() {
        $scraper = new Scraper();
        $news = $scraper->getAllPoliticalNews();
        require_once __DIR__ . '/../Views/index.php';
    }
}
?>
