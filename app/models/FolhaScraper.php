<?php
require_once 'AbstractNewsScraper.php';

class FolhaScraper extends AbstractNewsScraper {

    public function __construct() {
        $cacheFile = __DIR__ . '/../../cache/folha_news.json';
        $cacheTime = 600; // 10 minutos
        parent::__construct($cacheFile, $cacheTime);
        debug_log("[Folha] | Inicializado: Cache definido para 10 minutos.");
    }

    public function fetchNews(bool $forceUpdate = false): array {
        if (!$forceUpdate) {
            $cached = $this->getFromCache();
            if ($cached !== null) {
                debug_log("[Folha] | Cache: Utilizando dados do cache.");
                return $cached;
            }
        }
        
        debug_log("[Folha] | Scraping: Iniciando scraping da página de listagem.");
        $url = 'https://www1.folha.uol.com.br/poder/';
        $headers = [
            'User-Agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36",
            'Referer'    => "https://www.google.com/"
        ];
        
        $html = $this->getHtml($url, $headers);
        if ($html === null) {
            debug_log("[Folha] | Erro: Falha ao obter HTML da listagem.");
            return [];
        }
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $newsItems = [];
        
        // Seleciona os links que indicam um artigo de política (URLs que contenham "/poder/2025/" e terminem em ".shtml")
        $nodes = $xpath->query("//div[contains(@class, 'c-headline')]//a[contains(@href, '/poder/2025/')]");
        debug_log("[Folha] | Listagem: Nós encontrados = " . $nodes->length);
        
        $articleLinks = [];
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) continue;
            $link = $node->getAttribute('href');
            if ($link && strpos($link, '/poder/2025/') !== false && substr($link, -6) === '.shtml' && !in_array($link, $articleLinks)) {
                $articleLinks[] = $link;
            }
        }
        
        foreach ($articleLinks as $articleUrl) {
            $details = $this->scrapeArticle($articleUrl, $headers);
            if ($details) {
                $newsItems[] = $details;
            }
        }
        
        debug_log("[Folha] | Concluído: Scraping finalizado. Artigos encontrados = " . count($newsItems));
        $this->saveToCache($newsItems);
        return $newsItems;
    }
    
    private function scrapeArticle(string $articleUrl, array $headers): ?array {
        $html = $this->getHtml($articleUrl, $headers);
        if ($html === null) {
            debug_log("[Folha] | Erro: Falha ao obter HTML do artigo: " . $articleUrl);
            return null;
        }
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        
        // Título: tenta extrair do h1 com classe "c-content-head__title", ou usa meta og:title como fallback
        $titleNodes = $xpath->query("//h1[contains(@class, 'c-content-head__title')]");
        $title = $titleNodes->length > 0 ? trim($titleNodes->item(0)->nodeValue) : '';
        if (!$title) {
            $metaNodes = $xpath->query("//meta[@property='og:title']");
            $title = $metaNodes->length > 0 ? trim($metaNodes->item(0)->getAttribute('content')) : '';
        }
        
        // Descrição: extrai do h2 com classe "c-content-head__subtitle"
        $descNodes = $xpath->query("//h2[contains(@class, 'c-content-head__subtitle')]");
        $description = $descNodes->length > 0 ? trim($descNodes->item(0)->nodeValue) : 'Descrição não disponível.';
        
        // Data: tenta extrair de <time>; se não, usa meta article:published_time
        $timeNodes = $xpath->query("//time");
        $publishedAt = '';
        if ($timeNodes->length > 0) {
            $publishedAt = $timeNodes->item(0)->getAttribute('datetime');
        }
        if (!$publishedAt) {
            $metaTime = $xpath->query("//meta[@property='article:published_time']");
            $publishedAt = $metaTime->length > 0 ? $metaTime->item(0)->getAttribute('content') : 'Data não informada.';
        }
        
        // Autor: atualizamos o seletor para buscar dentro do container "c-news__wrap", "c-signature" e o <strong> com a classe "c-signature__author"
        $authorNodes = $xpath->query("//div[contains(@class, 'c-news__wrap')]//div[contains(@class, 'c-signature')]//strong[contains(@class, 'c-signature__author')]/a");
        $author = $authorNodes->length > 0 ? trim($authorNodes->item(0)->nodeValue) : 'Não disponível';
        
        if (!$title) {
            debug_log("[Folha] | Alerta: Título não extraído para artigo: " . $articleUrl);
            return null;
        }
        
        return [
            'title'       => $title,
            'url'         => $articleUrl,
            'description' => $description,
            'author'      => $author,
            'publishedAt' => $publishedAt,
            'source'      => 'Folha'
        ];
    }
}
?>
