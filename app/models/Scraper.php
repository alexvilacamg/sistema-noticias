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
        // Adiciona os scrapers de cada portal.
        $this->scrapers[] = new G1Scraper();
        $this->scrapers[] = new UOLScraper();
        // Para incluir novos portais, basta implementar a interface e adicioná-los aqui.
    }

    /**
     * Combina as notícias de todos os portais e normaliza o campo de data.
     *
     * @return array Array unificado de notícias.
     */
    public function getAllPoliticalNews(): array {
        $news = [];
        foreach ($this->scrapers as $scraper) {
            $news = array_merge($news, $scraper->fetchNews());
        }
        // Padroniza o campo "publishedAt" para o mesmo formato em todos os itens.
        foreach ($news as &$item) {
            if (!empty($item['publishedAt'])) {
                $item['publishedAt'] = $this->normalizeDate($item['publishedAt']);
            }
        }
        return $news;
    }

    /**
     * Normaliza a data para o formato ISO 8601.
     *
     * Se a data já estiver no formato ISO (contendo "T"), será apenas reformatada.
     * Se estiver no formato "d/m/Y Hhi" (ex: "10/04/2025 19h44"), o "h" é substituído por ":"
     * e a data é interpretada com o fuso de "America/Sao_Paulo".
     *
     * @param string $date Data original.
     * @return string Data no formato ISO 8601 (ex: "2025-04-10T19:44:00-03:00").
     */
    private function normalizeDate(string $date): string {
        // Se a data já contém "T", consideramos que ela está em formato ISO e reformatamos
        if (strpos($date, "T") !== false) {
            try {
                $dt = new DateTime($date);
                return $dt->format('Y-m-d\TH:i:sP');
            } catch (Exception $e) {
                return $date; // Retorna a data original se houver erro
            }
        } else {
            // Exemplo de data no formato "10/04/2025 19h44"
            $formatted = str_replace("h", ":", $date);
            $dt = DateTime::createFromFormat('d/m/Y H:i', $formatted, new DateTimeZone('America/Sao_Paulo'));
            if ($dt !== false) {
                return $dt->format('Y-m-d\TH:i:sP');
            } else {
                return $date; // Retorna a data original se o parsing falhar
            }
        }
    }
}
?>
