<?php
require_once __DIR__ . '/../factories/ScraperFactory.php';
// Se precisar, inclua 'require_once' de classes que não estejam em autoload.

class Scraper
{
    private $scrapers = [];

    public function __construct()
    {
        // Em vez de instanciar manualmente, carregamos via factory:
        $this->scrapers = ScraperFactory::createAllScrapers();
    }

    public function getAllPoliticalNews(bool $forceUpdate = false): array
    {
        $news = [];
        foreach ($this->scrapers as $scraper) {
            // Cada $scraper é algo que herda AbstractNewsScraper e implementa fetchNews()
            $news = array_merge($news, $scraper->fetchNews($forceUpdate));
        }

        // Normaliza datas (você já tem esse método):
        foreach ($news as &$item) {
            if (!empty($item['publishedAt'])) {
                $item['publishedAt'] = $this->normalizeDate($item['publishedAt']);
            }
        }
        return $news;
    }

    private function normalizeDate(string $date): string
    {
        // Aqui segue o código que você já tem
        $date = trim($date);
        if (strpos($date, "T") !== false) {
            try {
                $dt = new DateTime($date);
                return $dt->format('Y-m-d\TH:i:sP');
            } catch (Exception $e) {
                // tenta formatos abaixo
            }
        }
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $date, new DateTimeZone('America/Sao_Paulo'));
        if ($dt !== false) {
            return $dt->format('Y-m-d\TH:i:sP');
        }
        $dt = DateTime::createFromFormat('d/m/Y H:i:s', $date, new DateTimeZone('America/Sao_Paulo'));
        if ($dt !== false) {
            return $dt->format('Y-m-d\TH:i:sP');
        }
        $dt = DateTime::createFromFormat('d/m/Y H:i', $date, new DateTimeZone('America/Sao_Paulo'));
        if ($dt !== false) {
            return $dt->format('Y-m-d\TH:i:sP');
        }
        if (strtotime($date) !== false) {
            $dt = new DateTime($date);
            return $dt->format('Y-m-d\TH:i:sP');
        }
        return "1970-01-01T00:00:00+00:00";
    }
}
