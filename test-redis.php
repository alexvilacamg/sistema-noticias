<?php
// Salve como test-redis.php na raiz do projeto
require_once __DIR__ . '/vendor/autoload.php';

try {
    $redis = new \Predis\Client();
    echo "Conectando ao Redis...\n";
    
    // Tenta um comando simples
    $redis->set('test_key', 'Hello Redis!');
    $value = $redis->get('test_key');
    
    echo "Valor recuperado: $value\n";
    echo "Redis está funcionando corretamente!\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}