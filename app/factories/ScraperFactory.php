<?php

// Ajuste caminhos conforme necessário, mas em geral ficaria assim:
require_once __DIR__ . '/../../config/scrapers_config.php';

// IMPORTANTE: adicionar require_once para cada scraper que estiver na config
// se não estiver usando autoload PSR-4/Composer. 
require_once __DIR__ . '/../models/G1Scraper.php';
require_once __DIR__ . '/../models/UOLScraper.php';
require_once __DIR__ . '/../models/FolhaScraper.php';
// Se tiver EstadaoScraper, ou outros, inclua aqui também

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
        $scraperClasses = require __DIR__ . '/../../config/scrapers_config.php';

        $scrapers = [];
        foreach ($scraperClasses as $className) {
            // Agora class_exists($className) retornará true, pois já importamos as classes
            if (class_exists($className)) {
                // Instancia dinamicamente
                $scrapers[] = new $className();
            }
        }
        return $scrapers;
    }
}
