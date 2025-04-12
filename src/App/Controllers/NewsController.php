<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Controllers\NewsController.php

namespace App\Controllers;

use App\Models\Scraper;
use App\Views\Helpers\ViewHelper;
use App\Factories\RepositoryFactory;

class NewsController {
    public function index() {
        // Se forceUpdate=1, força atualização
        $forceUpdate = isset($_GET['forceUpdate']) && $_GET['forceUpdate'] == 1;
        
        // Se source=xxx, filtra por fonte
        $filters = [];
        if (isset($_GET['source']) && in_array($_GET['source'], ['G1', 'UOL', 'Folha'])) {
            $filters['source'] = $_GET['source'];
        }
        
        // Busca as notícias do banco em vez de usar o Scraper diretamente
        $repository = RepositoryFactory::createNewsRepository();
        
        // Verifica se precisa forçar atualização
        if ($forceUpdate) {
            // Usa o Scraper que agora já salva no banco
            $scraper = new Scraper();
            $news = $scraper->getAllPoliticalNews(true);
        } else {
            // Obtém direto do banco com filtros
            $news = $repository->getAll($filters);
        }
        
        $helper = new ViewHelper();
        
        // Inclui a view
        require_once __DIR__ . '/../Views/index.php';
    }
}
?>
