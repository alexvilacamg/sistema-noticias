<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Models\AbstractNewsScraper.php

namespace App\Models;

use App\Utils\HttpClient;
use App\Utils\Logger;

/**
 * Classe abstrata que cuida de:
 * - Cache (getFromCache / saveToCache)
 * - Obter HTML via HttpClient
 * - Criação de DOMXPath (createDomXPath)
 * - Logging padronizado (log)
 */
abstract class AbstractNewsScraper implements NewsScraperInterface
{
    protected $cacheFile;
    protected $cacheTime;

    public function __construct($cacheFile, $cacheTime = 600)
    {
        $this->cacheFile = $cacheFile;
        $this->cacheTime = $cacheTime;
    }

    /**
     * Tenta recuperar os dados do cache, se válido.
     */
    protected function getFromCache(): ?array
    {
        if (file_exists($this->cacheFile) && ((time() - filemtime($this->cacheFile)) < $this->cacheTime)) {
            $this->log("Utilizando cache do arquivo: " . $this->cacheFile);
            $data = file_get_contents($this->cacheFile);
            $newsItems = json_decode($data, true);
            if (is_array($newsItems)) {
                return $newsItems;
            }
        }
        return null;
    }

    /**
     * Salva os dados no cache.
     */
    protected function saveToCache(array $data): void
    {
        if (!is_dir(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0777, true);
        }
        file_put_contents($this->cacheFile, json_encode($data));
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
