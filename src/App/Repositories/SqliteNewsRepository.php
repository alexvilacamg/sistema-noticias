<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Repositories\SqliteNewsRepository.php

namespace App\Repositories;

use App\Utils\Logger;
use PDO;

class SqliteNewsRepository implements NewsRepositoryInterface
{
    private $db;
    
    public function __construct()
    {
        $dbPath = __DIR__ . '/../../../database.sqlite';
        $this->initializeDatabase($dbPath);
    }
    
    private function initializeDatabase(string $dbPath)
    {
        try {
            // Criar arquivo se não existir
            $dirPath = dirname($dbPath);
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0777, true);
            }
            
            $this->db = new PDO("sqlite:$dbPath");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Criar tabela se não existir
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS news (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    url TEXT UNIQUE NOT NULL,
                    description TEXT,
                    source TEXT NOT NULL,
                    author TEXT,
                    published_at TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } catch (\Exception $e) {
            Logger::error('Database initialization error: ' . $e->getMessage(), 'Repository');
            throw $e;
        }
    }
    
    public function getAll(array $filters = []): array
    {
        try {
            $query = "SELECT * FROM news";
            $params = [];
            
            // Adicionar filtros se existirem
            if (!empty($filters['source'])) {
                $query .= " WHERE source = :source";
                $params[':source'] = $filters['source'];
            }
            
            // Adicionar pesquisa de texto se especificada
            if (!empty($filters['search'])) {
                $whereClause = (strpos($query, 'WHERE') !== false) ? 'AND' : 'WHERE';
                $query .= " $whereClause (title LIKE :search OR description LIKE :search)";
                $params[':search'] = '%'.$filters['search'].'%';
            }
            
            $query .= " ORDER BY published_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Logger::error('Error getting all news: ' . $e->getMessage(), 'Repository');
            return [];
        }
    }
    
    public function findByUrl(string $url)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM news WHERE url = :url LIMIT 1");
            $stmt->bindValue(':url', $url);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Logger::error('Error finding news by URL: ' . $e->getMessage(), 'Repository');
            return null;
        }
    }
    
    public function save(array $data): bool
    {
        try {
            // Verificar se já existe
            $exists = $this->findByUrl($data['url']);
            
            if ($exists) {
                // Atualizar
                $stmt = $this->db->prepare("
                    UPDATE news SET 
                    title = :title,
                    description = :description,
                    source = :source,
                    author = :author,
                    published_at = :published_at,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE url = :url
                ");
            } else {
                // Inserir
                $stmt = $this->db->prepare("
                    INSERT INTO news (
                        title, url, description, source, author, published_at
                    ) VALUES (
                        :title, :url, :description, :source, :author, :published_at
                    )
                ");
            }
            
            // Definir parâmetros - usando bindValue em vez de bindParam
            $stmt->bindValue(':title', $data['title']);
            $stmt->bindValue(':url', $data['url']);
            $stmt->bindValue(':description', $data['description'] ?? '');
            $stmt->bindValue(':source', $data['source'] ?? 'Desconhecido');
            $stmt->bindValue(':author', $data['author'] ?? 'Desconhecido');
            $stmt->bindValue(':published_at', $data['publishedAt'] ?? date('Y-m-d H:i:s'));
            
            return $stmt->execute();
        } catch (\Exception $e) {
            Logger::error('Error saving news: ' . $e->getMessage(), 'Repository');
            return false;
        }
    }
    
    public function saveMany(array $newsItems): bool
    {
        try {
            $this->db->beginTransaction();
            
            foreach ($newsItems as $item) {
                if (!$this->save($item)) {
                    throw new \Exception("Failed to save item: " . json_encode($item));
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('Error saving multiple news: ' . $e->getMessage(), 'Repository');
            return false;
        }
    }
    
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM news WHERE id = :id");
            $stmt->bindValue(':id', $id);
            return $stmt->execute();
        } catch (\Exception $e) {
            Logger::error('Error deleting news: ' . $e->getMessage(), 'Repository');
            return false;
        }
    }
    
    public function clear(): bool
    {
        try {
            $this->db->exec("DELETE FROM news");
            return true;
        } catch (\Exception $e) {
            Logger::error('Error clearing news table: ' . $e->getMessage(), 'Repository');
            return false;
        }
    }
}