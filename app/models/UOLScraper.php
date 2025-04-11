<?php
require_once 'AbstractNewsScraper.php';

class UOLScraper extends AbstractNewsScraper {

    public function __construct() {
        $cacheFile = __DIR__ . '/../../cache/uol_news.json';
        $cacheTime = 600;
        parent::__construct($cacheFile, $cacheTime);
        debug_log("[UOL] | Inicializado: Cache definido para 10 minutos.");
    }

    public function fetchNews(bool $forceUpdate = false): array {
        if (!$forceUpdate) {
            $cached = $this->getFromCache();
            if ($cached !== null) {
                debug_log("[UOL] | Cache: Utilizando dados do cache.");
                return $cached;
            }
        }
        
        debug_log("[UOL] | Scraping: Iniciando scraping da página de listagem.");
        $url = 'https://noticias.uol.com.br/politica/';
        $headers = [
            'User-Agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36",
            'Referer' => "https://www.google.com/"
        ];
        
        $html = $this->getHtml($url, $headers);
        if ($html === null) {
            debug_log("[UOL] | Erro: Falha ao obter HTML da listagem.");
            return [];
        }
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $newsItems = [];
        
        // Seleciona os itens da listagem
        $nodes = $xpath->query("//div[contains(@class, 'flex-wrap')]//div[contains(@class, 'thumbnails-item') and not(contains(@class, 'itemAds'))]");
        debug_log("[UOL] | Listagem: Itens encontrados = " . $nodes->length);
        
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) continue;
            $aTag = $node->getElementsByTagName('a')->item(0);
            if (!$aTag) continue;
            $link = $aTag->getAttribute('href');
            
            $titleNodes = $xpath->query(".//h3[contains(@class, 'thumb-title')]", $node);
            $title = ($titleNodes->length > 0) ? trim($titleNodes->item(0)->nodeValue) : 'Sem título';
            
            $timeNodes = $xpath->query(".//time[contains(@class, 'thumb-date')]", $node);
            $publishedAt = ($timeNodes->length > 0) ? trim($timeNodes->item(0)->nodeValue) : 'Data não informada';
            
            $details = $this->scrapeArticle($link, $headers);
            if ($details) {
                $newsItems[] = [
                    'title'       => $title,
                    'url'         => $link,
                    'description' => $details['description'] ?? 'Descrição não disponível.',
                    'author'      => $details['author'] ?? 'Não disponível',
                    'publishedAt' => $publishedAt,
                    'source'      => 'UOL'
                ];
            } else {
                $newsItems[] = [
                    'title'       => $title,
                    'url'         => $link,
                    'description' => 'Descrição não disponível.',
                    'author'      => 'Não disponível',
                    'publishedAt' => $publishedAt,
                    'source'      => 'UOL'
                ];
            }
        }
        
        debug_log("[UOL] | Concluído: Scraping finalizado. Artigos encontrados = " . count($newsItems));
        $this->saveToCache($newsItems);
        return $newsItems;
    }
    
    private function scrapeArticle(string $articleUrl, array $headers): ?array {
        $html = $this->getHtml($articleUrl, $headers);
        if ($html === null) {
            debug_log("[UOL] | Erro: Falha ao obter HTML do artigo: " . $articleUrl);
            return null;
        }
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        
        // Extrai o primeiro parágrafo do container "jupiter-paragraph-fragment"
        $paraNodes = $xpath->query("//div[contains(@class, 'jupiter-paragraph-fragment')]//p");
        $description = ($paraNodes->length > 0) ? trim($paraNodes->item(0)->nodeValue) : 'Descrição não disponível.';
        
        // Extrai o nome do autor a partir do link com a classe "solar-author-name"
        $authorNodes = $xpath->query("//div[contains(@class, 'solar-author-names')]//a[contains(@class, 'solar-author-name')]");
        $author = ($authorNodes->length > 0) ? trim($authorNodes->item(0)->nodeValue) : 'Não disponível';
        
        return [
            'description' => $description,
            'author'      => $author
        ];
    }
}
?>
