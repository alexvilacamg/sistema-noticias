<?php
// Script para normalizar os campos no banco de dados

require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Utils\Logger;

echo "Normalizando campos no banco de dados...\n";

try {
    // Conectar ao SQLite
    $dbPath = __DIR__ . '/database.sqlite';
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obter todas as notícias
    $stmt = $db->query("SELECT * FROM news");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Encontradas " . count($news) . " notícias no banco de dados.\n";
    
    // Verificar e atualizar registros que precisam de normalização
    $count = 0;
    foreach ($news as $item) {
        // Se o campo published_at estiver vazio ou NULL, podemos ter um problema
        if (empty($item['published_at']) || $item['published_at'] == '1970-01-01T00:00:00+00:00') {
            echo "ID " . $item['id'] . ": Campo published_at vazio ou inválido.\n";
            $count++;
            
            // Atualizar o registro se possível
            $stmt = $db->prepare("UPDATE news SET published_at = :date WHERE id = :id");
            $stmt->bindValue(':date', date('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $item['id']);
            $stmt->execute();
        }
    }
    
    echo "Total de " . $count . " registros normalizados.\n";
    echo "Normalização concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}