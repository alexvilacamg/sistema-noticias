<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\create_sqlite_indexes.php

require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "Criando índices para SQLite...\n";

try {
    $dbPath = __DIR__ . '/database.sqlite';
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Índice para buscas por título
    echo "Criando índice para título...\n";
    $db->exec("CREATE INDEX IF NOT EXISTS idx_news_title ON news(title)");
    
    // Índice composto para filtragem por fonte e data
    echo "Criando índice composto para fonte e data...\n";
    $db->exec("CREATE INDEX IF NOT EXISTS idx_news_source_date ON news(source, published_at)");
    
    // Criar tabela virtual FTS5 para busca em texto completo
    echo "Criando tabela FTS5 para busca em texto completo...\n";
    $db->exec("
    CREATE VIRTUAL TABLE IF NOT EXISTS news_fts USING fts5(
        title, description, content='news', content_rowid='id'
    )");
    
    // Verificar se a tabela FTS já tem dados
    $count = $db->query("SELECT COUNT(*) FROM news_fts")->fetchColumn();
    
    if ($count == 0) {
        echo "Populando tabela FTS com dados existentes...\n";
        // Preencher a tabela FTS com os dados existentes
        $db->exec("
        INSERT INTO news_fts(rowid, title, description)
        SELECT id, title, description FROM news
        ");
    }
    
    // Criar trigger para manter a tabela FTS atualizada em inserções
    $db->exec("
    CREATE TRIGGER IF NOT EXISTS news_ai AFTER INSERT ON news BEGIN
        INSERT INTO news_fts(rowid, title, description)
        VALUES (new.id, new.title, new.description);
    END;
    ");
    
    // Criar trigger para manter a tabela FTS atualizada em atualizações
    $db->exec("
    CREATE TRIGGER IF NOT EXISTS news_au AFTER UPDATE ON news BEGIN
        UPDATE news_fts SET
            title = new.title,
            description = new.description
        WHERE rowid = old.id;
    END;
    ");
    
    // Criar trigger para manter a tabela FTS atualizada em exclusões
    $db->exec("
    CREATE TRIGGER IF NOT EXISTS news_ad AFTER DELETE ON news BEGIN
        DELETE FROM news_fts WHERE rowid = old.id;
    END;
    ");
    
    echo "Índices criados com sucesso!\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Trace: \n" . $e->getTraceAsString() . "\n";
}