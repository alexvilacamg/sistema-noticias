<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Cache\RedisCache.php

namespace App\Cache;

use Predis\Client;
use App\Utils\Logger;

class RedisCache implements CacheInterface
{
    private $redis;
    private $prefix;
    private $defaultTtl;
    
    /**
     * Cria uma nova instância de RedisCache
     * 
     * @param array $config Configuração do Redis
     */
    public function __construct(array $config = [])
    {
        $this->prefix = $config['prefix'] ?? 'news_';
        $this->defaultTtl = $config['ttl'] ?? 600;
        
        try {
            $this->redis = new Client([
                'scheme' => $config['scheme'] ?? 'tcp',
                'host'   => $config['host'] ?? '127.0.0.1',
                'port'   => $config['port'] ?? 6379,
                'password' => $config['password'] ?? null,
                'database' => $config['database'] ?? 0,
            ]);
            
            // Teste a conexão
            $this->redis->ping();
            Logger::info('Redis cache iniciado com sucesso', 'Cache');
        } catch (\Exception $e) {
            Logger::error('Falha ao inicializar o Redis: ' . $e->getMessage(), 'Cache');
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
            $value = $this->redis->get($this->key($key));
            
            if ($value === null) {
                return $default;
            }
            
            $decoded = json_decode($value, true);
            return $decoded === null ? $value : $decoded;
        } catch (\Exception $e) {
            Logger::error('Erro ao recuperar do Redis: ' . $e->getMessage(), 'Cache');
            return $default;
        }
    }
    
    public function set(string $key, $value, int $ttl = 0): bool
    {
        if ($ttl === 0) {
            $ttl = $this->defaultTtl;
        }
        
        try {
            $value = is_scalar($value) && !is_string($value) ? $value : json_encode($value);
            $this->redis->setex($this->key($key), $ttl, $value);
            return true;
        } catch (\Exception $e) {
            Logger::error('Erro ao gravar no Redis: ' . $e->getMessage(), 'Cache');
            return false;
        }
    }
    
    public function has(string $key): bool
    {
        try {
            return (bool) $this->redis->exists($this->key($key));
        } catch (\Exception $e) {
            Logger::error('Erro ao verificar existência no Redis: ' . $e->getMessage(), 'Cache');
            return false;
        }
    }
    
    public function delete(string $key): bool
    {
        try {
            $this->redis->del($this->key($key));
            return true;
        } catch (\Exception $e) {
            Logger::error('Erro ao excluir do Redis: ' . $e->getMessage(), 'Cache');
            return false;
        }
    }
    
    public function clear(): bool
    {
        try {
            // Limpa apenas as chaves com o prefixo
            $keys = $this->redis->keys($this->prefix . '*');
            if (count($keys) > 0) {
                $this->redis->del($keys);
            }
            return true;
        } catch (\Exception $e) {
            Logger::error('Erro ao limpar o Redis: ' . $e->getMessage(), 'Cache');
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