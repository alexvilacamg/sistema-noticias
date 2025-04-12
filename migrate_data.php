<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\migrate_data.php

require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Factories\RepositoryFactory;
use App\Utils\Logger;

// Função para a migração
function migrateData() {
    echo "Iniciando migração de dados...\n";
    
    $cacheFile = __DIR__ . '/cache/all_news.json';
    if (!file_exists($cacheFile)) {
        echo "Arquivo de cache não encontrado. Nada para migrar.\n";
        return false;
    }
    
    try {
        $repository = RepositoryFactory::createNewsRepository();
        
        // Carregar dados do arquivo JSON
        $jsonData = file_get_contents($cacheFile);
        $newsItems = json_decode($jsonData, true);
        
        if (!$newsItems || !is_array($newsItems)) {
            echo "O arquivo de cache está vazio ou inválido.\n";
            return false;
        }
        
        echo "Encontradas " . count($newsItems) . " notícias para migrar...\n";
        
        // Salvar no banco de dados
        $result = $repository->saveMany($newsItems);
        
        if ($result) {
            echo "Migração concluída com sucesso!\n";
            // Renomear o arquivo original para backup
            rename($cacheFile, $cacheFile . '.bak');
            echo "Backup do arquivo original criado como {$cacheFile}.bak\n";
        } else {
            echo "Falha na migração. Verifique os logs.\n";
        }
        
        return $result;
    } catch (Exception $e) {
        echo "Erro durante a migração: " . $e->getMessage() . "\n";
        Logger::error("Erro na migração: " . $e->getMessage(), "Migration");
        return false;
    }
}

// Executar a migração
migrateData();