<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\test_search.php

require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Termo de pesquisa do argumento da linha de comando
$searchTerm = $argv[1] ?? 'política';

echo "Pesquisando por: '$searchTerm'\n\n";

try {
    $repository = App\Factories\RepositoryFactory::createNewsRepository();
    
    // Pesquisa usando o novo filtro
    $results = $repository->getAll(['search' => $searchTerm]);
    
    echo "Resultados encontrados: " . count($results) . "\n\n";
    
    // Exibe os 5 primeiros resultados
    foreach (array_slice($results, 0, 5) as $index => $news) {
        echo ($index + 1) . ". " . $news['title'] . "\n";
        echo "   Fonte: " . $news['source'] . " | Data: " . $news['published_at'] . "\n";
        echo "   URL: " . $news['url'] . "\n\n";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Trace: \n" . $e->getTraceAsString() . "\n";
}