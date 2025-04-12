<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\public\api\cache.php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Cache\CacheManager;
use App\Utils\Logger;

header('Content-Type: application/json');

// Verifica se é uma solicitação POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'clear':
            $result = CacheManager::clearNewsCache();
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Cache limpo com sucesso' : 'Falha ao limpar o cache'
            ]);
            break;
            
        case 'warm':
            $result = CacheManager::warmNewsCache();
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Cache pré-aquecido com sucesso' : 'Falha ao pré-aquecer o cache'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Ação desconhecida'
            ]);
    }
} else {
    // Para solicitações GET, retorna estatísticas
    $stats = CacheManager::getStats();
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}