<?php
require_once 'G1Scraper.php';
require_once 'UOLScraper.php';

class Scraper {

    /**
     * Array que armazenará os scrapers de cada portal.
     *
     * @var NewsScraperInterface[]
     */
    private $scrapers = [];

    public function __construct() {
        $this->scrapers[] = new G1Scraper();
        $this->scrapers[] = new UOLScraper();
        // Para incluir novos portais, basta implementar a interface e adicioná-los aqui.
    }

    /**
     * Combina as notícias de todos os portais e normaliza o campo de data.
     * Se $forceUpdate for true, os scrapers ignorarão o cache.
     *
     * @param bool $forceUpdate
     * @return array Array unificado de notícias.
     */
    public function getAllPoliticalNews(bool $forceUpdate = false): array {
        $news = [];
        foreach ($this->scrapers as $scraper) {
            $news = array_merge($news, $scraper->fetchNews($forceUpdate));
        }
        // Normaliza a data de publicação para o mesmo formato (ISO 8601)
        foreach ($news as &$item) {
            if (!empty($item['publishedAt'])) {
                $item['publishedAt'] = $this->normalizeDate($item['publishedAt']);
            }
        }
        return $news;
    }

    /**
     * Normaliza a data para o formato ISO 8601.
     * Se a data já tiver "T", reformatamos; caso contrário, assumimos o formato "d/m/Y Hhi".
     *
     * @param string $date
     * @return string
     */
    private function normalizeDate(string $date): string {
        if (strpos($date, "T") !== false) {
            try {
                $dt = new DateTime($date);
                return $dt->format('Y-m-d\TH:i:sP');
            } catch (Exception $e) {
                return $date;
            }
        } else {
            $formatted = str_replace("h", ":", $date);
            $dt = DateTime::createFromFormat('d/m/Y H:i', $formatted, new DateTimeZone('America/Sao_Paulo'));
            if ($dt !== false) {
                return $dt->format('Y-m-d\TH:i:sP');
            } else {
                return $date;
            }
        }
    }
}
?>
