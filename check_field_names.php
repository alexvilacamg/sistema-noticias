<?php
require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "=== VERIFICAÇÃO DE NOMENCLATURA ===\n";

try {
    // Obter o repositório
    $repository = App\Factories\RepositoryFactory::createNewsRepository();
    
    // Buscar todas as notícias
    $news = $repository->getAll();
    
    echo "Analisando " . count($news) . " notícias...\n";
    $withPublishedAt = 0;
    $withPublishedAtSnakeCase = 0;
    
    foreach ($news as $index => $item) {
        echo "Notícia #" . ($index + 1) . ": ";
        echo "'" . substr($item['title'], 0, 30) . "...'\n";
        
        // Verificar campos
        echo "   - ";
        if (array_key_exists('publishedAt', $item)) {
            echo "Usa 'publishedAt' (camelCase)\n";
            $withPublishedAt++;
        }
        if (array_key_exists('published_at', $item)) {
            echo "Usa 'published_at' (snake_case)\n";
            $withPublishedAtSnakeCase++;
        }
        if (!array_key_exists('publishedAt', $item) && !array_key_exists('published_at', $item)) {
            echo "ERRO: Nenhum campo de data encontrado!\n";
        }
    }
    
    echo "\n=== RESULTADO DA VERIFICAÇÃO ===\n";
    echo "Total de notícias: " . count($news) . "\n";
    echo "Com 'publishedAt' (camelCase): $withPublishedAt\n";
    echo "Com 'published_at' (snake_case): $withPublishedAtSnakeCase\n";
    
    if ($withPublishedAt > 0) {
        echo "\nATENÇÃO: Ainda existem $withPublishedAt notícias usando o formato antigo 'publishedAt'.\n";
        echo "Execute o script normalize_fields.php para completar a migração.\n";
    } else {
        echo "\nTodas as notícias estão usando o formato padronizado 'published_at'. ✓\n";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}