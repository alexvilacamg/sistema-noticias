<?php
require_once 'NewsScraperInterface.php';

class G1Scraper implements NewsScraperInterface {

    private $cacheFile;
    private $cacheTime;

    public function __construct() {
        // Define o caminho e o tempo de cache (600 segundos = 10 minutos)
        $this->cacheFile = __DIR__ . '/../../cache/g1_news.json';
        $this->cacheTime = 600;
    }

    public function fetchNews(): array {
        // Se o cache existir e for válido, lê os dados
        if (file_exists($this->cacheFile) && (time() - filemtime($this->cacheFile)) < $this->cacheTime) {
            $data = file_get_contents($this->cacheFile);
            $newsItems = json_decode($data, true);
            if (is_array($newsItems)) {
                return $newsItems;
            }
        }
        
        // Caso contrário, realiza o scraping da página de listagem do G1
        $url = 'https://g1.globo.com/politica/';
        $opts = [
            'http' => [
                'method'  => 'GET',
                'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) " .
                             "AppleWebKit/537.36 (KHTML, like Gecko) " .
                             "Chrome/105.0.0.0 Safari/537.36\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $html = file_get_contents($url, false, $context);
        if ($html === false) {
            return [];
        }
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $newsItems = [];
        
        // Seleciona os links dos artigos na página de listagem
        $nodes = $xpath->query("//a[contains(@class, 'feed-post-link')]");
        $articleLinks = [];
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }
            $link = $node->getAttribute('href');
            if ($link && !in_array($link, $articleLinks)) {
                $articleLinks[] = $link;
            }
        }
        
        // Para cada link, obtém os detalhes do artigo
        foreach ($articleLinks as $articleUrl) {
            $details = $this->scrapeArticle($articleUrl);
            if ($details) {
                $newsItems[] = $details;
            }
        }
        
        // Armazena o resultado no cache
        if (!is_dir(__DIR__ . '/../../cache')) {
            mkdir(__DIR__ . '/../../cache', 0777, true);
        }
        file_put_contents($this->cacheFile, json_encode($newsItems));
        
        return $newsItems;
    }
    
    /**
     * Realiza o scraping dos detalhes de um artigo no G1.
     *
     * @param string $articleUrl URL do artigo detalhado
     * @return array|null Dados do artigo ou null se não conseguir extrair o título.
     */
    private function scrapeArticle($articleUrl) {
        $opts = [
            'http' => [
                'method'  => 'GET',
                'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) " .
                             "AppleWebKit/537.36 (KHTML, like Gecko) " .
                             "Chrome/105.0.0.0 Safari/537.36\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $html = file_get_contents($articleUrl, false, $context);
        if ($html === false) {
            return null;
        }
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Título: procura dentro do container "mc-article-header" o h1 com itemprop="headline"
        $titleNodes = $xpath->query("//div[contains(@class, 'mc-article-header')]//h1[@itemprop='headline']");
        $title = $titleNodes->length > 0 ? trim($titleNodes->item(0)->nodeValue) : '';
        
        // Descrição: tenta extrair o h2 com classe "content-head__subtitle" e itemprop "alternativeHeadline"
        $descNodes = $xpath->query("//div[contains(@class, 'mc-article-header')]//h2[contains(@class, 'content-head__subtitle') and @itemprop='alternativeHeadline']");
        $description = $descNodes->length > 0 ? trim($descNodes->item(0)->nodeValue) : 'Descrição não disponível.';
        
        // Data de Publicação: extrai o datetime do elemento <time> com itemprop="datePublished"
        $timeNodes = $xpath->query("//div[contains(@class, 'mc-article-header')]//time[@itemprop='datePublished']");
        $publishedAt = '';
        if ($timeNodes->length > 0) {
            $publishedAt = $timeNodes->item(0)->getAttribute('datetime');
        }
        
        // Autor: busca dentro do parágrafo com classe "content-publication-data__from"
        $authorNodes = $xpath->query("//div[contains(@class, 'mc-article-header')]//p[contains(@class, 'content-publication-data__from')]");
        $author = '';
        if ($authorNodes->length > 0) {
            $aNodes = $xpath->query(".//a", $authorNodes->item(0));
            if ($aNodes->length > 0) {
                $author = trim($aNodes->item(0)->nodeValue);
            }
        }
        
        if (!$title) {
            return null;
        }
        
        return [
            'title'       => $title,
            'url'         => $articleUrl,
            'description' => $description,
            'author'      => $author ?: 'Não disponível',
            'publishedAt' => $publishedAt ?: 'Data não informada.',
            'source'      => 'G1'
        ];
    }
}
?>
