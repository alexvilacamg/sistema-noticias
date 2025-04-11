<?php
require_once 'NewsScraperInterface.php';

class UOLScraper implements NewsScraperInterface {

    /**
     * Busca as últimas notícias da editoria de Política do UOL.
     * Para cada artigo listado, é feita uma requisição adicional para obter
     * a descrição (primeiro parágrafo) e o nome do autor diretamente da página da matéria.
     *
     * @return array Array de notícias.
     */
    public function fetchNews(): array {
        $url = 'https://noticias.uol.com.br/politica/';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Permite redirecionamentos e desabilita verificação SSL (para ambiente de desenvolvimento)
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36',
            'Referer: https://www.google.com/'
        ]);
        $html = curl_exec($ch);
        if ($html === false) {
            debug_log("UOLScraper: Falha ao obter HTML da página de listagem: " . curl_error($ch));
            curl_close($ch);
            return [];
        }
        curl_close($ch);
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $newsItems = [];
        
        // Seleciona os itens de notícia dentro do container "flex-wrap", ignorando os anúncios (itemAds)
        $nodes = $xpath->query("//div[contains(@class, 'flex-wrap')]//div[contains(@class, 'thumbnails-item') and not(contains(@class, 'itemAds'))]");
        debug_log("UOLScraper: Nós encontrados na listagem: " . $nodes->length);
        
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) continue;
            
            // Obtém a tag <a> que contém o link do artigo
            $aTag = $node->getElementsByTagName('a')->item(0);
            if (!$aTag) continue;
            $link = $aTag->getAttribute('href');
            
            // Título: busca o <h3> com classe que contenha "thumb-title"
            $titleNodes = $xpath->query(".//h3[contains(@class, 'thumb-title')]", $node);
            $title = ($titleNodes->length > 0) ? trim($titleNodes->item(0)->nodeValue) : 'Sem título';
            
            // Data de publicação: extrai o texto do <time> com classe "thumb-date"
            $timeNodes = $xpath->query(".//time[contains(@class, 'thumb-date')]", $node);
            $publishedAt = ($timeNodes->length > 0) ? trim($timeNodes->item(0)->nodeValue) : 'Data não informada';
            
            // Obtém os detalhes adicionais da página do artigo
            $details = $this->scrapeArticle($link);
            if ($details) {
                $newsItems[] = [
                    'title'       => $title,
                    'url'         => $link,
                    'description' => $details['description'] ?? 'Descrição não disponível.',
                    'author'      => $details['author'] ?? 'Não disponível',
                    'publishedAt' => $publishedAt, // Você pode optar por usar a data extraída do artigo, se preferir.
                    'source'      => 'UOL'
                ];
            } else {
                // Se falhar a obtenção dos detalhes, mantém os dados da listagem
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
        
        return $newsItems;
    }
    
    /**
     * Realiza o scraping da página do artigo para extrair o primeiro parágrafo (descrição)
     * e o nome do autor da notícia.
     *
     * @param string $articleUrl URL do artigo
     * @return array|null Array com 'description' e 'author' ou null em caso de falha.
     */
    private function scrapeArticle(string $articleUrl): ?array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $articleUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36',
            'Referer: https://www.google.com/'
        ]);
        $html = curl_exec($ch);
        if ($html === false) {
            debug_log("UOLScraper: Falha ao obter HTML do artigo: " . $articleUrl . ". cURL error: " . curl_error($ch));
            curl_close($ch);
            return null;
        }
        curl_close($ch);
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        
        // Descrição: pega o primeiro parágrafo dentro do container da matéria.
        // Usamos o container "jupiter-paragraph-fragment" (ajuste se necessário).
        $paraNodes = $xpath->query("//div[contains(@class, 'jupiter-paragraph-fragment')]//p");
        $description = ($paraNodes->length > 0) ? trim($paraNodes->item(0)->nodeValue) : 'Descrição não disponível.';
        
        // Autor: pega o nome do autor a partir do link com a classe "solar-author-name".
        $authorNodes = $xpath->query("//div[contains(@class, 'solar-author-names')]//a[contains(@class, 'solar-author-name')]");
        if ($authorNodes->length > 0) {
            $author = trim($authorNodes->item(0)->nodeValue);
        } else {
            $author = 'Não disponível';
        }
        
        return [
            'description' => $description,
            'author'      => $author
        ];
    }
}
?>
