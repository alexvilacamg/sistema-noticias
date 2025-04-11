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
        
        // Seleciona cada bloco <li> com a classe "c-headline c-headline--newslist"
        $nodes = $xpath->query("//li[contains(@class, 'c-headline') and contains(@class, 'c-headline--newslist')]");
        debug_log("[Folha] | Listagem: Nós encontrados = " . $nodes->length);
        
        foreach ($nodes as $node) {
            // Link principal
            $linkNode = $xpath->query(".//div[contains(@class,'c-headline__content')]/a", $node);
            if (!$linkNode || $linkNode->length === 0) {
                continue;
            }
            $articleUrl = $linkNode->item(0)->getAttribute('href');

            // Título
            $titleNode = $xpath->query(".//h2[contains(@class,'c-headline__title')]", $node);
            $title = '';
            if ($titleNode && $titleNode->length > 0) {
                $title = trim($titleNode->item(0)->nodeValue);
            }

            // Descrição
            $descNode = $xpath->query(".//p[contains(@class,'c-headline__standfirst')]", $node);
            $description = 'Descrição não disponível.';
            if ($descNode && $descNode->length > 0) {
                $description = trim($descNode->item(0)->nodeValue);
            }

            // Data
            $timeNode = $xpath->query(".//time[contains(@class,'c-headline__dateline')]", $node);
            $publishedAt = 'Data não informada.';
            if ($timeNode && $timeNode->length > 0) {
                // Primeiro tenta pegar do atributo datetime
                $publishedAtAttr = trim($timeNode->item(0)->getAttribute('datetime'));
                if ($publishedAtAttr) {
                    $publishedAt = $publishedAtAttr;
                } else {
                    // Se não existir, pega o texto dentro de <time>
                    $publishedAtText = trim($timeNode->item(0)->nodeValue);
                    if ($publishedAtText) {
                        $publishedAt = $publishedAtText;
                    }
                }
            }

            // Autor (a listagem normalmente não exibe, então padronizamos)
            $author = 'Não disponível';

            // Só adiciona se tiver título e link
            if ($title && $articleUrl) {
                $newsItems[] = [
                    'title'       => $title,
                    'url'         => $articleUrl,
                    'description' => $description,
                    'author'      => $author,
                    'publishedAt' => $publishedAt,
                    'source'      => 'Folha'
                ];
            }
        }
        
        debug_log("[Folha] | Concluído: Scraping finalizado. Artigos encontrados = " . count($newsItems));
        $this->saveToCache($newsItems);
        return $newsItems;
    }
}
