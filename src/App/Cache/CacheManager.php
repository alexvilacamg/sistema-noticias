<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Cache\CacheManager.php

namespace App\Cache;

use App\Utils\Logger;
use App\Factories\RepositoryFactory;

class CacheManager
{
    /**
     * Limpa o cache de notícias
     * 
     * @return bool Sucesso da operação
     */
    public static function clearNewsCache(): bool
    {
        try {
            // Limpa o cache do Redis/Memcached/File
            $cache = getCache();
            $cacheResult = $cache->clear();
            
            // Limpa o banco de dados também
            $repository = RepositoryFactory::createNewsRepository();
            $dbResult = $repository->clear();
            
            $success = $cacheResult && $dbResult;
            
            if ($success) {
                Logger::info('Cache e banco de dados de notícias limpos com sucesso', 'CacheManager');
            } else {
                Logger::error('Falha ao limpar cache ou banco de dados de notícias', 'CacheManager');
            }
            
            return $success;
        } catch (\Exception $e) {
            Logger::error('Erro ao limpar cache: ' . $e->getMessage(), 'CacheManager');
            return false;
        }
    }
    
    /**
     * Pré-aquece o cache buscando os dados
     * 
     * @return bool Sucesso da operação
     */
    public static function warmNewsCache(): bool
    {
        try {
            Logger::info('Iniciando pré-aquecimento do cache e banco', 'CacheManager');
            
            $scraper = new \App\Models\Scraper();
            $news = $scraper->getAllPoliticalNews(true);
            
            Logger::info('Cache e banco pré-aquecido com ' . count($news) . ' notícias', 'CacheManager');
            return true;
        } catch (\Exception $e) {
            Logger::error('Erro no pré-aquecimento: ' . $e->getMessage(), 'CacheManager');
            return false;
        }
    }
    
    /**
     * Retorna estatísticas do cache
     * 
     * @return array Estatísticas
     */
    public static function getStats(): array
    {
        $cache = getCache();
        $stats = [
            'type' => CACHE_TYPE,
            'ttl' => CACHE_TTL,
            'status' => 'ativo'
        ];
        
        // Para Redis e Memcached, podemos adicionar mais estatísticas
        if (CACHE_TYPE === 'redis' && $cache instanceof RedisCache) {
            try {
                // Exemplo, precisaria ser implementado na classe RedisCache
                // $info = $cache->getRedisInfo();
                // $stats['memory_used'] = $info['used_memory_human'];
                // $stats['total_keys'] = $info['db0'];
            } catch (\Exception $e) {
                $stats['error'] = $e->getMessage();
            }
        }
        
        return $stats;
    }
}