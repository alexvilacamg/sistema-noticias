<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Cache\CacheInterface.php

namespace App\Cache;

interface CacheInterface
{
    /**
     * Recupera um item do cache
     * 
     * @param string $key A chave do item
     * @param mixed $default Valor padrão caso o item não exista
     * @return mixed O item do cache ou o valor padrão
     */
    public function get(string $key, $default = null);
    
    /**
     * Armazena um item no cache
     * 
     * @param string $key A chave do item
     * @param mixed $value O valor a ser armazenado
     * @param int $ttl Tempo de vida em segundos
     * @return bool Sucesso da operação
     */
    public function set(string $key, $value, int $ttl = 600): bool;
    
    /**
     * Verifica se um item existe no cache e não expirou
     * 
     * @param string $key A chave do item
     * @return bool Verdadeiro se o item existir
     */
    public function has(string $key): bool;
    
    /**
     * Remove um item do cache
     * 
     * @param string $key A chave do item
     * @return bool Sucesso da operação
     */
    public function delete(string $key): bool;
    
    /**
     * Limpa todo o cache
     * 
     * @return bool Sucesso da operação
     */
    public function clear(): bool;
    
    /**
     * Obtém ou calcula um valor
     * 
     * @param string $key A chave do item
     * @param int $ttl Tempo de vida em segundos
     * @param callable $callback Função para calcular o valor se não existir
     * @return mixed O valor armazenado ou calculado
     */
    public function remember(string $key, int $ttl, callable $callback);
}