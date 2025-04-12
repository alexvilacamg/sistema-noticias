<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Factories\ScraperFactory.php

namespace App\Factories;

use App\Models\G1Scraper;
use App\Models\UOLScraper;
use App\Models\FolhaScraper;

class ScraperFactory
{
    /**
     * Lê config/scrapers_config.php e instancia cada classe listada.
     *
     * @return array Array de instâncias de scrapers
     */
    public static function createAllScrapers(): array
    {
        // Carrega o array de nomes de classes do config
        $scraperClasses = require __DIR__ . '/../../../config/scrapers_config.php';

        $scrapers = [];
        foreach ($scraperClasses as $className) {
            // Constrói o nome completo da classe com namespace
            $fullClassName = 'App\\Models\\' . $className;
            
            // Verifica se a classe existe
            if (class_exists($fullClassName)) {
                // Instancia dinamicamente
                $scrapers[] = new $fullClassName();
            }
        }
        return $scrapers;
    }
}
