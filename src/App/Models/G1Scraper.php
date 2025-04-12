<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Models\G1Scraper.php

namespace App\Models;

require_once 'AbstractNewsScraper.php';

class G1Scraper extends AbstractNewsScraper
{
    public function __construct()
    {
        $cacheFile = __DIR__ . '/../../../cache/g1_news.json';
        $cacheTime = 600; // 10 minutos
        parent::__construct($cacheFile, $cacheTime);
        $this->log("[G1] | Inicializado: Cache definido para 10 minutos.");
    }

    public function fetchNews(bool $forceUpdate = false): array
    {
        if (!$forceUpdate) {
            $cached = $this->getFromCache();
            if ($cached !== null) {
                $this->log("[G1] | Cache: Utilizando dados do cache.");
                return $cached;
            }
        }
        
        $this->log("[G1] | Scraping: Iniciando scraping da página de listagem.");
        $url = 'https://g1.globo.com/politica/';
        $headers = [
            'User-Agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36"
        ];

        $html = $this->getHtml($url, $headers);
        if ($html === null) {
            $this->log("[G1] | Erro: Falha ao obter HTML da listagem.");
            return [];
        }
        
        // Em vez do DOMDocument manual, usamos:
        $xpath = $this->createDomXPath($html);
        if (!$xpath) {
            $this->log("[G1] | Erro: Falha ao criar DOMXPath.");
            return [];
        }

        $newsItems = [];
        
        // Seleciona os links dos artigos na página de listagem
        $nodes = $xpath->query("//a[contains(@class, 'feed-post-link')]");
        $this->log("[G1] | Listagem: Nós encontrados = " . $nodes->length, LOG_INFO);
        
        $articleLinks = [];
        foreach ($nodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }
            $link = $node->getAttribute('href');
            if ($link && !in_array($link, $articleLinks)) {
                $articleLinks[] = $link;
            }
        }
        
        foreach ($articleLinks as $articleUrl) {
            $details = $this->scrapeArticle($articleUrl, $headers);
            if ($details) {
                $newsItems[] = $details;
            }
        }
        
        $this->log("[G1] | Concluído: Scraping finalizado. Artigos encontrados = " . count($newsItems));
        $this->saveToCache($newsItems);
        return $newsItems;
    }
    
    private function scrapeArticle(string $articleUrl, array $headers): ?array
    {
        $html = $this->getHtml($articleUrl, $headers);
        if ($html === null) {
            $this->log("[G1] | Erro: Falha ao obter HTML do artigo: " . $articleUrl);
            return null;
        }
        
        // Cria DOMXPath via método auxiliar
        $xpath = $this->createDomXPath($html);
        if (!$xpath) {
            $this->log("[G1] | Erro: DOMXPath nulo no artigo: " . $articleUrl);
            return null;
        }
        
        // Título
        $titleNodes = $xpath->query("//div[contains(@class, 'mc-article-header')]//h1[@itemprop='headline']");
        $title = $titleNodes->length > 0 ? trim($titleNodes->item(0)->nodeValue) : '';
        
        // Descrição
        $descNodes = $xpath->query("//div[contains(@class, 'mc-article-header')]//h2[contains(@class, 'content-head__subtitle') and @itemprop='alternativeHeadline']");
        $description = $descNodes->length > 0 ? trim($descNodes->item(0)->nodeValue) : 'Descrição não disponível.';

        // Data de publicação
        $timeNodes = $xpath->query("//div[contains(@class, 'mc-article-header')]//time[@itemprop='datePublished']");
        $publishedAt = $timeNodes->length > 0 ? $timeNodes->item(0)->getAttribute('datetime') : 'Data não informada.';
        
        // Autor
        $author = '';
        $authorNodes = $xpath->query("//div[contains(@class, 'mc-article-header')]//p[contains(@class, 'content-publication-data__from')]");
        if ($authorNodes->length > 0) {
            $aNodes = $xpath->query(".//a", $authorNodes->item(0));
            if ($aNodes->length > 0) {
                $author = trim($aNodes->item(0)->nodeValue);
            }
        }
        
        if (!$title) {
            $this->log("[G1] | Alerta: Título não extraído para artigo: " . $articleUrl);
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
