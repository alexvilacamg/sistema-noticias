<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Cache\MemcachedCache.php

namespace App\Cache;

use App\Utils\Logger;

class MemcachedCache implements CacheInterface
{
    private $memcached;
    private $prefix;
    private $defaultTtl;
    
    /**
     * Cria uma nova instância de MemcachedCache
     * 
     * @param array $config Configuração do Memcached
     */
    public function __construct(array $config = [])
    {
        $this->prefix = $config['prefix'] ?? 'news_';
        $this->defaultTtl = $config['ttl'] ?? 600;
        
        try {
            $this->memcached = new \Memcached();
            
            // Adiciona os servidores (pode ser um array de servidores para cluster)
            $servers = $config['servers'] ?? [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100]
            ];
            
            foreach ($servers as $server) {
                $this->memcached->addServer(
                    $server['host'],
                    $server['port'],
                    $server['weight'] ?? 100
                );
            }
            
            // Configura opções
            $this->memcached->setOption(\Memcached::OPT_COMPRESSION, true);
            $this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            
            Logger::info('Memcached cache iniciado com sucesso', 'Cache');
        } catch (\Exception $e) {
            Logger::error('Falha ao inicializar o Memcached: ' . $e->getMessage(), 'Cache');
            throw $e;
        }
    }
    
    /**
     * Retorna a chave completa com o prefixo
     */
    private function key(string $key): string
    {
        return $this->prefix . $key;
    }
    
    public function get(string $key, $default = null)
    {
        try {
            $value = $this->memcached->get($this->key($key));
            
            if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
                return $default;
            }
            
            return $value;
        } catch (\Exception $e) {
            Logger::error('Erro ao recuperar do Memcached: ' . $e->getMessage(), 'Cache');
            return $default;
        }
    }
    
    public function set(string $key, $value, int $ttl = 0): bool
    {
        if ($ttl === 0) {
            $ttl = $this->defaultTtl;
        }
        
        try {
            return $this->memcached->set($this->key($key), $value, $ttl);
        } catch (\Exception $e) {
            Logger::error('Erro ao gravar no Memcached: ' . $e->getMessage(), 'Cache');
            return false;
        }
    }
    
    public function has(string $key): bool
    {
        try {
            $this->memcached->get($this->key($key));
            return $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND;
        } catch (\Exception $e) {
            Logger::error('Erro ao verificar existência no Memcached: ' . $e->getMessage(), 'Cache');
            return false;
        }
    }
    
    public function delete(string $key): bool
    {
        try {
            return $this->memcached->delete($this->key($key));
        } catch (\Exception $e) {
            Logger::error('Erro ao excluir do Memcached: ' . $e->getMessage(), 'Cache');
            return false;
        }
    }
    
    public function clear(): bool
    {
        try {
            // Infelizmente, Memcached não tem método para limpar apenas chaves com prefixo
            // então precisamos limpar tudo ou manter um registro das chaves
            return $this->memcached->flush();
        } catch (\Exception $e) {
            Logger::error('Erro ao limpar o Memcached: ' . $e->getMessage(), 'Cache');
            return false;
        }
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