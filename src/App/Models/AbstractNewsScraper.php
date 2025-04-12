<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Models\AbstractNewsScraper.php

namespace App\Models;

use App\Utils\HttpClient;
use App\Utils\Logger;
use App\Cache\CacheInterface;

/**
 * Classe abstrata que cuida de:
 * - Cache (getFromCache / saveToCache)
 * - Obter HTML via HttpClient
 * - Criação de DOMXPath (createDomXPath)
 * - Logging padronizado (log)
 */
abstract class AbstractNewsScraper implements NewsScraperInterface
{
    protected $cacheKey;
    protected $cacheTtl;
    protected $cache;

    public function __construct(string $cacheKey, int $cacheTtl = 600)
    {
        $this->cacheKey = $cacheKey;
        $this->cacheTtl = $cacheTtl;
        $this->cache = getCache(); // Função definida em config.php
    }

    /**
     * Tenta recuperar os dados do cache, se válido.
     */
    protected function getFromCache(): ?array
    {
        $result = $this->cache->get($this->cacheKey);
        
        if ($result !== null) {
            $this->log("Cache: Utilizando dados do cache para " . $this->cacheKey);
            return $result;
        }
        
        return null;
    }

    /**
     * Salva os dados no cache.
     */
    protected function saveToCache(array $data): void
    {
        $this->cache->set($this->cacheKey, $data, $this->cacheTtl);
        $this->log("Cache: Dados salvos com TTL de {$this->cacheTtl} segundos");
    }

    /**
     * Obtém o HTML de uma URL usando HttpClient.
     */
    protected function getHtml(string $url, array $headers = []): ?string
    {
        $this->log("getHtml: Buscando HTML de " . $url);
        return HttpClient::get($url, $headers);
    }

    /**
     * Cria um DOMDocument e DOMXPath a partir de uma string HTML, já tratando encoding e supressão de erros.
     */
    protected function createDomXPath(string $html): ?\DOMXPath
    {
        // Converter encoding para evitar problemas de caracteres
        $convmap = [0x80, 0x10FFFF, 0, 0x10FFFF];
        $html = mb_encode_numericentity($html, $convmap, 'UTF-8');

        $dom = new \DOMDocument(); // Note o \ para indicar namespace global
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        return new \DOMXPath($dom); // Note o \ para indicar namespace global
    }

    /**
     * Método simples de log, para centralizar prefixos.
     */
    protected function log(string $message, $level = 'INFO'): void
    {
        Logger::log($message, $level, get_class($this));
    }
}
