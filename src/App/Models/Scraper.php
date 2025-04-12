<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Models\Scraper.php

namespace App\Models;

use App\Factories\ScraperFactory;
use App\Factories\RepositoryFactory;
use App\Repositories\NewsRepositoryInterface;

class Scraper
{
    private $scrapers = [];
    private $progressCallback = null;
    private $repository;

    public function __construct($progressCallback = null)
    {
        $this->scrapers = ScraperFactory::createAllScrapers();
        $this->progressCallback = $progressCallback;
        $this->repository = RepositoryFactory::createNewsRepository();
    }

    private function reportProgress($message) {
        if (is_callable($this->progressCallback)) {
            call_user_func($this->progressCallback, $message);
        }
    }

    public function getAllPoliticalNews(bool $forceUpdate = false): array
    {
        $news = [];
        
        if (!$forceUpdate) {
            // Tentar carregar do banco de dados
            $news = $this->repository->getAll();
            if (!empty($news)) {
                $this->reportProgress("Usando dados existentes do banco (" . count($news) . " notícias)");
                return $news;
            }
        }
        
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
            } catch (\Exception $e) {
                $this->reportProgress("ERRO em " . $scraperName . ": " . $e->getMessage());
            }
        }

        $this->reportProgress("Normalizando datas de " . count($news) . " notícias...");
        // Padronizar para usar apenas published_at
        foreach ($news as &$item) {
            if (isset($item['publishedAt']) && !isset($item['published_at'])) {
                // Converter de publishedAt para published_at
                $item['published_at'] = $this->normalizeDate($item['publishedAt']);
                // Remover o campo antigo para evitar duplicidade
                unset($item['publishedAt']);
            } elseif (isset($item['published_at'])) {
                // Normalizar o campo correto
                $item['published_at'] = $this->normalizeDate($item['published_at']);
            } else {
                // Garantir que sempre haja um campo published_at
                $item['published_at'] = date('Y-m-d\TH:i:sP');
            }
        }
        
        // Salvar no banco de dados
        $this->reportProgress("Salvando " . count($news) . " notícias no banco de dados...");
        $this->repository->saveMany($news);
        
        $this->reportProgress("Processamento completo. Total de notícias: " . count($news));
        return $news;
    }

    private function normalizeDate(string $date): string
    {
        // Se a data for um texto como "Data não informada.", retorna null
        if ($date === 'Data não informada.' || empty(trim($date))) {
            return "1970-01-01T00:00:00+00:00"; // Data padrão para não disponível
        }
        
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
