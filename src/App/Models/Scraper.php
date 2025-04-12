<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Models\Scraper.php

namespace App\Models;

use App\Factories\ScraperFactory;

class Scraper
{
    private $scrapers = [];
    private $progressCallback = null;

    public function __construct($progressCallback = null)
    {
        // Em vez de instanciar manualmente, carregamos via factory:
        $this->scrapers = ScraperFactory::createAllScrapers();
        // Armazena o callback para reportar progresso
        $this->progressCallback = $progressCallback;
    }

    private function reportProgress($message) {
        if (is_callable($this->progressCallback)) {
            call_user_func($this->progressCallback, $message);
        }
    }

    public function getAllPoliticalNews(bool $forceUpdate = false): array
    {
        $news = [];
        $this->reportProgress("Iniciando coleta de notícias políticas...");
        
        foreach ($this->scrapers as $scraper) {
            $scraperName = get_class($scraper);
            $this->reportProgress("Processando fonte: " . $scraperName);
            
            try {
                $startTime = microtime(true);
                $sourceNews = $scraper->fetchNews($forceUpdate);
                $endTime = microtime(true);
                $timeSpent = round($endTime - $startTime, 2);
                
                $this->reportProgress("Concluído " . $scraperName . ": " . count($sourceNews) . " notícias em " . $timeSpent . "s");
                $news = array_merge($news, $sourceNews);
            } catch (\Exception $e) { // Note o namespace global para Exception
                $this->reportProgress("ERRO em " . $scraperName . ": " . $e->getMessage());
            }
        }

        $this->reportProgress("Normalizando datas de " . count($news) . " notícias...");
        // Normaliza datas
        foreach ($news as &$item) {
            if (!empty($item['publishedAt'])) {
                $item['publishedAt'] = $this->normalizeDate($item['publishedAt']);
            }
        }
        
        $this->reportProgress("Processamento completo. Total de notícias: " . count($news));
        return $news;
    }

    private function normalizeDate(string $date): string
    {
        $date = trim($date);
        if (strpos($date, "T") !== false) {
            try {
                $dt = new \DateTime($date); // Note o uso de \ para indicar namespace global
                return $dt->format('Y-m-d\TH:i:sP');
            } catch (\Exception $e) { // Também namespace global para Exception
                // tenta formatos abaixo
            }
        }
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $date, new \DateTimeZone('America/Sao_Paulo'));
        if ($dt !== false) {
            return $dt->format('Y-m-d\TH:i:sP');
        }
        $dt = \DateTime::createFromFormat('d/m/Y H:i:s', $date, new \DateTimeZone('America/Sao_Paulo'));
        if ($dt !== false) {
            return $dt->format('Y-m-d\TH:i:sP');
        }
        $dt = \DateTime::createFromFormat('d/m/Y H:i', $date, new \DateTimeZone('America/Sao_Paulo'));
        if ($dt !== false) {
            return $dt->format('Y-m-d\TH:i:sP');
        }
        if (strtotime($date) !== false) {
            $dt = new \DateTime($date);
            return $dt->format('Y-m-d\TH:i:sP');
        }
        return "1970-01-01T00:00:00+00:00";
    }
}
