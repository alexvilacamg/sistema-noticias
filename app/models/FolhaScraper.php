<?php
require_once 'AbstractNewsScraper.php';

class FolhaScraper extends AbstractNewsScraper
{
    public function __construct()
    {
        $cacheFile = __DIR__ . '/../../cache/folha_news.json';
        $cacheTime = 600; // 10 minutos
        parent::__construct($cacheFile, $cacheTime);
        $this->log("[Folha] | Inicializado: Cache definido para 10 minutos.");
    }

    public function fetchNews(bool $forceUpdate = false): array
    {
        if (!$forceUpdate) {
            $cached = $this->getFromCache();
            if ($cached !== null) {
                $this->log("[Folha] | Cache: Utilizando dados do cache.");
                return $cached;
            }
        }
        
        $this->log("[Folha] | Scraping: Iniciando scraping da página de listagem.");
        $url = 'https://www1.folha.uol.com.br/poder/';
        $headers = [
            'User-Agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36",
            'Referer'    => "https://www.google.com/"
        ];
        
        $html = $this->getHtml($url, $headers);
        if ($html === null) {
            $this->log("[Folha] | Erro: Falha ao obter HTML da listagem.");
            return [];
        }
        
        $xpath = $this->createDomXPath($html);
        if (!$xpath) {
            $this->log("[Folha] | Erro: Falha ao criar DOMXPath.");
            return [];
        }

        $newsItems = [];
        
        // Seleciona cada bloco <li> com a classe "c-headline c-headline--newslist"
        $nodes = $xpath->query("//li[contains(@class, 'c-headline') and contains(@class, 'c-headline--newslist')]");
        $this->log("[Folha] | Listagem: Nós encontrados = " . $nodes->length);
        
        foreach ($nodes as $node) {
            // Extrai URL do artigo
            $linkNode = $xpath->query(".//div[contains(@class,'c-headline__content')]/a", $node);
            if (!$linkNode || $linkNode->length === 0) {
                continue;
            }
            $articleUrl = $linkNode->item(0)->getAttribute('href');

            // Título (pego da listagem)
            $titleNode = $xpath->query(".//h2[contains(@class,'c-headline__title')]", $node);
            $title = '';
            if ($titleNode && $titleNode->length > 0) {
                $title = trim($titleNode->item(0)->nodeValue);
            }

            // Descrição (pego da listagem)
            $descNode = $xpath->query(".//p[contains(@class,'c-headline__standfirst')]", $node);
            $description = 'Descrição não disponível.';
            if ($descNode && $descNode->length > 0) {
                $description = trim($descNode->item(0)->nodeValue);
            }

            // Data (pego da listagem)
            $timeNode = $xpath->query(".//time[contains(@class,'c-headline__dateline')]", $node);
            $publishedAt = 'Data não informada.';
            if ($timeNode && $timeNode->length > 0) {
                // Primeiro tenta pegar do atributo datetime
                $publishedAtAttr = trim($timeNode->item(0)->getAttribute('datetime'));
                if ($publishedAtAttr) {
                    $publishedAt = $publishedAtAttr;
                } else {
                    // Se não existir, pega o texto dentro de <time>
                    $timeText = trim($timeNode->item(0)->nodeValue);
                    if ($timeText) {
                        $publishedAt = $timeText;
                    }
                }
            }

            // Autor (normalmente não aparece no listing, então vamos buscar na página do artigo)
            $author = 'Não disponível';

            // Agora buscamos dados detalhados no artigo individual
            $articleDetails = $this->scrapeArticle($articleUrl, $headers);
            if ($articleDetails) {
                // Se os detalhes tiverem autor, data ou até título/descrição melhores, use-os
                if (!empty($articleDetails['author'])) {
                    $author = $articleDetails['author'];
                }
                // Se quiser sobrepor título/descrição/data, também pode:
                // if (!empty($articleDetails['title'])) { $title = $articleDetails['title']; }
                // if (!empty($articleDetails['description'])) { $description = $articleDetails['description']; }
                // if (!empty($articleDetails['publishedAt']) && $articleDetails['publishedAt'] !== 'Data não informada.') {
                //    $publishedAt = $articleDetails['publishedAt'];
                // }
            }

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
        
        $this->log("[Folha] | Concluído: Scraping finalizado. Artigos encontrados = " . count($newsItems));
        $this->saveToCache($newsItems);
        return $newsItems;
    }

    /**
     * Faz uma segunda requisição para o artigo e extrai autor, data, etc.
     */
    private function scrapeArticle(string $articleUrl, array $headers): ?array
    {
        $this->log("[Folha] | scrapeArticle: Buscando HTML do artigo: " . $articleUrl);
        $html = $this->getHtml($articleUrl, $headers);
        if ($html === null) {
            $this->log("[Folha] | scrapeArticle: Erro ao obter HTML do artigo: " . $articleUrl);
            return null;
        }

        $xpath = $this->createDomXPath($html);
        if (!$xpath) {
            $this->log("[Folha] | scrapeArticle: Falha ao criar DOMXPath no artigo: " . $articleUrl);
            return null;
        }

        // Exemplo de como pegar o autor:
        //   <div class="c-news__wrap">
        //     <div class="c-signature">
        //       <strong class="c-signature__author">
        //         <a href="...">Catia Seabra</a>
        //       </strong>
        //     </div>
        //   </div>
        $authorNodes = $xpath->query("//div[contains(@class, 'c-news__wrap')]//div[contains(@class, 'c-signature')]//strong[contains(@class, 'c-signature__author')]/a");
        $author = ($authorNodes->length > 0)
            ? trim($authorNodes->item(0)->nodeValue)
            : 'Não disponível';

        $this->log("[Folha] | scrapeArticle: Autor extraído = " . $author);

        // Se quiser, pode extrair título, descrição, data daqui também.
        // Exemplo rápido (ajuste se for preciso):
        $titleNodes = $xpath->query("//h1[contains(@class, 'c-content-head__title')]");
        $title = $titleNodes->length > 0 ? trim($titleNodes->item(0)->nodeValue) : '';

        // Retorna só o que for precisar
        return [
            'author' => $author,
            'title'  => $title,
            // 'description' => ...,
            // 'publishedAt' => ...
        ];
    }
}
