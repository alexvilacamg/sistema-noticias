<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Repositories\NewsRepositoryInterface.php

namespace App\Repositories;

interface NewsRepositoryInterface
{
    /**
     * Retorna todas as notícias
     */
    public function getAll(array $filters = []): array;
    
    /**
     * Busca uma notícia pelo URL
     */
    public function findByUrl(string $url);
    
    /**
     * Salva uma notícia (nova ou existente)
     */
    public function save(array $data): bool;
    
    /**
     * Salva múltiplas notícias de uma vez
     */
    public function saveMany(array $newsItems): bool;
    
    /**
     * Exclui uma notícia
     */
    public function delete(int $id): bool;
    
    /**
     * Limpa todas as notícias
     */
    public function clear(): bool;
}