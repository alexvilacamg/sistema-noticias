<?php
require_once 'G1Scraper.php';
require_once 'UOLScraper.php';
require_once 'FolhaScraper.php';

class Scraper {

    private $scrapers = [];

    public function __construct() {
        $this->scrapers[] = new G1Scraper();
        $this->scrapers[] = new UOLScraper();
        $this->scrapers[] = new FolhaScraper();
    }

    public function getAllPoliticalNews(bool $forceUpdate = false): array {
        $news = [];
        foreach ($this->scrapers as $scraper) {
            $news = array_merge($news, $scraper->fetchNews($forceUpdate));
        }
        foreach ($news as &$item) {
            if (!empty($item['publishedAt'])) {
                $item['publishedAt'] = $this->normalizeDate($item['publishedAt']);
            }
        }
        return $news;
    }

    private function normalizeDate(string $date): string {
        $date = trim($date);
        if (strpos($date, "T") !== false) {
            try {
                $dt = new DateTime($date);
                return $dt->format('Y-m-d\TH:i:sP');
            } catch (Exception $e) {
                // tenta os outros formatos abaixo
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
?>
