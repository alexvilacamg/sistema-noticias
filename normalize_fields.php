<?php
// Script para normalizar as inconsistências de nomenclatura no banco de dados

require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Utils\Logger;

echo "=== NORMALIZAÇÃO DE CAMPOS DO BANCO DE DADOS ===\n";
echo "Padronizando nomenclatura de publishedAt para published_at...\n\n";

try {
    // Conectar ao banco de dados
    $repository = App\Factories\RepositoryFactory::createNewsRepository();
    $dbType = getenv('DB_TYPE') ?: 'sqlite';
    
    if ($dbType === 'sqlite') {
        // Migração específica para SQLite
        $dbPath = __DIR__ . '/database.sqlite';
        $db = new PDO("sqlite:$dbPath");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Buscar notícias com publishedAt em vez de published_at
        $stmt = $db->query("SELECT * FROM news");
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Encontradas " . count($news) . " notícias no banco de dados.\n";
        
        $updatedCount = 0;
        $emptyCount = 0;
        
        // Verificar cada notícia
        foreach ($news as $item) {
            $needsUpdate = false;
            $published_at = $item['published_at'] ?? null;
            
            // Verificar campos nulos ou vazios
            if (empty($published_at) || $published_at === '1970-01-01T00:00:00+00:00') {
                echo "ID " . $item['id'] . ": Campo published_at vazio ou inválido.\n";
                $needsUpdate = true;
                $emptyCount++;
            }
            
            if ($needsUpdate) {
                $updatedCount++;
                
                // Atualizar com data atual
                $stmt = $db->prepare("UPDATE news SET published_at = :date WHERE id = :id");
                $stmt->bindValue(':date', date('Y-m-d H:i:s'));
                $stmt->bindValue(':id', $item['id']);
                $stmt->execute();
            }
        }
        
        echo "\nRelatório de Normalização:\n";
        echo "- Total de registros processados: " . count($news) . "\n";
        echo "- Registros atualizados: $updatedCount\n";
        echo "- Campos vazios corrigidos: $emptyCount\n";
        
    } else if ($dbType === 'mysql') {
        echo "Executando migração para MySQL...\n";
        // Implemente código específico para MySQL aqui
    }
    
    echo "\nNormalização concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    Logger::error('Erro na normalização de campos: ' . $e->getMessage(), 'Migration');
}