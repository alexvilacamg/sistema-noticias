<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\test_db_connection.php

require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "Testando conexão com o banco de dados...\n";
echo "Tipo: " . (getenv('DB_TYPE') ?: 'mysql') . "\n";
echo "Host: " . (getenv('DB_HOST') ?: '127.0.0.1') . "\n";
echo "Porta: " . (getenv('DB_PORT') ?: (getenv('DB_TYPE') == 'mongodb' ? '27017' : '3306')) . "\n";
echo "Banco: " . (getenv('DB_DATABASE') ?: 'sistema_noticias') . "\n\n";

try {
    $repository = App\Factories\RepositoryFactory::createNewsRepository();
    
    // Fazer uma operação simples para testar
    $allNews = $repository->getAll();
    
    echo "Conexão estabelecida com sucesso!\n";
    echo "Total de notícias no banco: " . count($allNews) . "\n";
    
    echo "\nDetalhes da primeira notícia:\n";
    if (!empty($allNews)) {
        print_r($allNews[0]);
    } else {
        echo "Não há notícias no banco ainda.\n";
    }
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Trace: \n" . $e->getTraceAsString() . "\n";
}