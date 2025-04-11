<?php
require_once 'NewsScraperInterface.php';
require_once __DIR__ . '/../utils/HttpClient.php';

abstract class AbstractNewsScraper implements NewsScraperInterface {
    protected $cacheFile;
    protected $cacheTime;

    public function __construct($cacheFile, $cacheTime = 600) {
        $this->cacheFile = $cacheFile;
        $this->cacheTime = $cacheTime;
    }

    /**
     * Tenta recuperar os dados do cache, se válido.
     */
    protected function getFromCache(): ?array {
        if (file_exists($this->cacheFile) && ((time() - filemtime($this->cacheFile)) < $this->cacheTime)) {
            debug_log("[Cache] | Utilizando cache do arquivo: " . $this->cacheFile);
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
    protected function saveToCache(array $data): void {
        if (!is_dir(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0777, true);
        }
        file_put_contents($this->cacheFile, json_encode($data));
    }

    /**
     * Obtém o HTML de uma URL usando a camada unificada de requisição.
     */
    protected function getHtml(string $url, array $headers = []): ?string {
        return HttpClient::get($url, $headers);
    }
}
?>
