<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Cache\FileCache.php

namespace App\Cache;

use App\Utils\Logger;

class FileCache implements CacheInterface
{
    private $cacheDir;
    private $prefix;
    private $defaultTtl;
    
    /**
     * Cria uma nova instância de FileCache
     * 
     * @param array $config Configuração do cache de arquivo
     */
    public function __construct(array $config = [])
    {
        $this->prefix = $config['prefix'] ?? 'news_';
        $this->defaultTtl = $config['ttl'] ?? 600;
        $this->cacheDir = $config['directory'] ?? __DIR__ . '/../../../cache/';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
            Logger::info('Diretório de cache criado: ' . $this->cacheDir, 'Cache');
        }
        
        Logger::info('File cache iniciado com sucesso', 'Cache');
    }
    
    /**
     * Retorna o caminho completo do arquivo de cache
     */
    private function getFilename(string $key): string
    {
        return $this->cacheDir . $this->prefix . md5($key) . '.cache';
    }
    
    public function get(string $key, $default = null)
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $content = file_get_contents($filename);
        if ($content === false) {
            Logger::error('Erro ao ler o arquivo de cache: ' . $filename, 'Cache');
            return $default;
        }
        
        $data = json_decode($content, true);
        
        // Verifica se o cache expirou
        if ($data['expires'] < time()) {
            $this->delete($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    public function set(string $key, $value, int $ttl = 0): bool
    {
        if ($ttl === 0) {
            $ttl = $this->defaultTtl;
        }
        
        $filename = $this->getFilename($key);
        
        $data = [
            'expires' => time() + $ttl,
            'value' => $value
        ];
        
        $result = file_put_contents($filename, json_encode($data));
        
        if ($result === false) {
            Logger::error('Erro ao gravar o arquivo de cache: ' . $filename, 'Cache');
            return false;
        }
        
        return true;
    }
    
    public function has(string $key): bool
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        // Verifica se o cache expirou
        $content = file_get_contents($filename);
        if ($content === false) {
            return false;
        }
        
        $data = json_decode($content, true);
        
        if ($data['expires'] < time()) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
    
    public function delete(string $key): bool
    {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    public function clear(): bool
    {
        $files = glob($this->cacheDir . $this->prefix . '*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    public function remember(string $key, int $ttl, callable $callback)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }
}