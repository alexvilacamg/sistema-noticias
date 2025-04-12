<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Repositories\MysqlNewsRepository.php

namespace App\Repositories;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utils\Logger;

class MysqlNewsRepository implements NewsRepositoryInterface
{
    public function __construct()
    {
        $this->setupConnection();
    }
    
    private function setupConnection()
    {
        $capsule = new DB;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_DATABASE') ?: 'sistema_noticias',
            'username' => getenv('DB_USERNAME') ?: 'news_user',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
    
    public function getAll(array $filters = []): array
    {
        try {
            $query = DB::table('news')
                ->select(
                    'news.id', 'news.title', 'news.url', 'news.description', 
                    'news.published_at', 'sources.name as source', 'authors.name as author'
                )
                ->leftJoin('sources', 'news.source_id', '=', 'sources.id')
                ->leftJoin('authors', 'news.author_id', '=', 'authors.id')
                ->orderBy('news.published_at', 'desc');
            
            // Aplicar filtros
            if (!empty($filters['source'])) {
                $query->where('sources.name', $filters['source']);
            }
            
            return $query->get()->toArray();
        } catch (\Exception $e) {
            Logger::error('Erro ao buscar notícias: ' . $e->getMessage(), 'Repository');
            return [];
        }
    }
    
    public function findByUrl(string $url)
    {
        try {
            return DB::table('news')
                ->select(
                    'news.id', 'news.title', 'news.url', 'news.description', 
                    'news.published_at', 'sources.name as source', 'authors.name as author'
                )
                ->leftJoin('sources', 'news.source_id', '=', 'sources.id')
                ->leftJoin('authors', 'news.author_id', '=', 'authors.id')
                ->where('news.url', $url)
                ->first();
        } catch (\Exception $e) {
            Logger::error('Erro ao buscar notícia por URL: ' . $e->getMessage(), 'Repository');
            return null;
        }
    }
    
    public function save(array $data): bool
    {
        try {
            DB::beginTransaction();
            
            // Buscar ou criar fonte
            $sourceId = $this->getOrCreateSource($data['source'] ?? 'Desconhecido');
            
            // Buscar ou criar autor
            $authorId = $this->getOrCreateAuthor($data['author'] ?? 'Desconhecido');
            
            // Verificar se a notícia já existe
            $existing = DB::table('news')->where('url', $data['url'])->first();
            
            // Garantir uso consistente de published_at
            $published_at = $data['published_at'] ?? $data['publishedAt'] ?? null;
            
            if ($existing) {
                // Atualizar
                DB::table('news')
                    ->where('id', $existing->id)
                    ->update([
                        'title' => $data['title'],
                        'description' => $data['description'] ?? '',
                        'published_at' => $published_at,
                        'source_id' => $sourceId,
                        'author_id' => $authorId,
                        'updated_at' => now()
                    ]);
            } else {
                // Inserir nova
                DB::table('news')->insert([
                    'title' => $data['title'],
                    'url' => $data['url'],
                    'description' => $data['description'] ?? '',
                    'published_at' => $published_at,
                    'source_id' => $sourceId,
                    'author_id' => $authorId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Logger::error('Erro ao salvar notícia: ' . $e->getMessage(), 'Repository');
            return false;
        }
    }
    
    public function saveMany(array $newsItems): bool
    {
        try {
            DB::beginTransaction();
            
            foreach ($newsItems as $item) {
                $this->save($item);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Logger::error('Erro ao salvar múltiplas notícias: ' . $e->getMessage(), 'Repository');
            return false;
        }
    }
    
    public function delete(int $id): bool
    {
        try {
            return DB::table('news')->where('id', $id)->delete() > 0;
        } catch (\Exception $e) {
            Logger::error('Erro ao excluir notícia: ' . $e->getMessage(), 'Repository');
            return false;
        }
    }
    
    public function clear(): bool
    {
        try {
            DB::table('news')->truncate();
            return true;
        } catch (\Exception $e) {
            Logger::error('Erro ao limpar notícias: ' . $e->getMessage(), 'Repository');
            return false;
        }
    }
    
    private function getOrCreateSource(string $sourceName): int
    {
        $source = DB::table('sources')->where('name', $sourceName)->first();
        if ($source) {
            return $source->id;
        }
        
        return DB::table('sources')->insertGetId([
            'name' => $sourceName,
            'website' => $this->getWebsiteFromSource($sourceName),
            'created_at' => now()
        ]);
    }
    
    private function getOrCreateAuthor(string $authorName): int
    {
        $author = DB::table('authors')->where('name', $authorName)->first();
        if ($author) {
            return $author->id;
        }
        
        return DB::table('authors')->insertGetId([
            'name' => $authorName,
            'created_at' => now()
        ]);
    }
    
    private function getWebsiteFromSource(string $sourceName): string
    {
        switch ($sourceName) {
            case 'G1': return 'https://g1.globo.com/';
            case 'UOL': return 'https://noticias.uol.com.br/';
            case 'Folha': return 'https://www1.folha.uol.com.br/';
            default: return 'https://exemplo.com.br/';
        }
    }
}