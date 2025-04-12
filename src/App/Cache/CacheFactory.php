<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Cache\CacheFactory.php

namespace App\Cache;

use App\Utils\Logger;

class CacheFactory
{
    /**
     * Cria uma instância de cache com base no tipo especificado
     * 
     * @param string $type Tipo de cache (redis, memcached, file)
     * @param array $config Configurações
     * @return CacheInterface Instância da implementação
     */
    public static function create(string $type = 'file', array $config = []): CacheInterface
    {
        $type = strtolower($type);
        
        try {
            switch ($type) {
                case 'redis':
                    if (class_exists('\Predis\Client')) {
                        return new RedisCache($config);
                    }
                    Logger::warning('Predis não encontrado, usando fallback', 'Cache');
                    return self::create('file', $config);
                    
                case 'memcached':
                    if (class_exists('\Memcached')) {
                        return new MemcachedCache($config);
                    }
                    Logger::warning('Memcached não encontrado, usando fallback', 'Cache');
                    return self::create('file', $config);
                    
                case 'file':
                default:
                    return new FileCache($config);
            }
        } catch (\Exception $e) {
            Logger::error('Erro ao criar cache: ' . $e->getMessage(), 'Cache');
            return new FileCache($config);
        }
    }
}