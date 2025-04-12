<?php
// Desabilitar o limite de tempo de execução do script
set_time_limit(0);

// Configurações iniciais para garantir que a saída seja enviada corretamente
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);

// Headers específicos para Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Para servidores Nginx

// Desativa qualquer buffer de saída para garantir envio imediato
@ob_end_clean();
if (ob_get_level() == 0) ob_start();

// Carrega o autoloader do Composer
require_once __DIR__ . '/../../vendor/autoload.php';

// Usa o namespace correto
use App\Models\Scraper;

// Função para enviar evento para o cliente
function sendEvent($message, $type = "progress") {
    echo "event: {$type}\n";
    echo "data: " . json_encode($message) . "\n\n";
    ob_flush();
    flush();
}

// Envia um heartbeat a cada 15 segundos para manter a conexão viva
function startHeartbeat() {
    ignore_user_abort(true); // continua executando mesmo se o cliente desconectar
    
    register_shutdown_function(function() {
        // Envia um evento de erro se o script terminar inesperadamente
        sendEvent(['status' => 'error', 'message' => 'O servidor finalizou a conexão inesperadamente'], 'error');
    });
    
    // Inicia o heartbeat
    $lastHeartbeat = time();
    return $lastHeartbeat;
}

// Inicia o heartbeat
$lastHeartbeat = startHeartbeat();

try {
    // Envia mensagem inicial
    sendEvent(['status' => 'start', 'message' => 'Iniciando processo de atualização...'], 'start');
    
    // Registra o início do processo para calcular o tempo total
    $startTime = microtime(true);
    
    // Define um callback personalizado para o Scraper que também verifica heartbeat
    $progressCallback = function($message) use (&$lastHeartbeat) {
        // Envia update de progresso
        sendEvent(['status' => 'progress', 'message' => $message]);
        
        // Verifica se precisa enviar heartbeat (a cada 15 segundos)
        $now = time();
        if ($now - $lastHeartbeat >= 15) {
            sendEvent(['status' => 'heartbeat', 'timestamp' => $now], 'heartbeat');
            $lastHeartbeat = $now;
        }
    };
    
    // Instancia o scraper com o callback personalizado
    $scraper = new Scraper($progressCallback);
    
    // Envia mensagem antes de começar o scraping
    sendEvent(['status' => 'progress', 'message' => 'Iniciando scraping dos portais de notícias...']);
    
    // Força a atualização do scraping
    $articles = $scraper->getAllPoliticalNews(true);
    
    // Cria o diretório de cache se não existir
    $cacheDir = __DIR__ . '/../../cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
        sendEvent(['status' => 'progress', 'message' => 'Diretório de cache criado']);
    }
    
    // Salva os artigos no cache
    $cacheFile = $cacheDir . '/all_news.json';
    $articlesCount = count($articles);
    sendEvent(['status' => 'progress', 'message' => "Salvando {$articlesCount} artigos no cache..."]);
    file_put_contents($cacheFile, json_encode($articles));
    
    // Calcula estatísticas
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    // Contagem por fonte
    $sourceStats = [];
    foreach ($articles as $article) {
        $source = $article['source'] ?? 'Desconhecido';
        if (!isset($sourceStats[$source])) {
            $sourceStats[$source] = 0;
        }
        $sourceStats[$source]++;
    }
    
    // Envia mensagem de conclusão
    sendEvent([
        'status' => 'success',
        'message' => 'Atualização realizada com sucesso!',
        'articles_count' => $articlesCount,
        'execution_time' => $executionTime,
        'sources' => $sourceStats,
        'cache_size' => filesize($cacheFile),
        'timestamp' => date('Y-m-d H:i:s')
    ], 'complete');
    
} catch (Exception $e) {
    // Captura qualquer exceção e envia como evento de erro
    sendEvent([
        'status' => 'error',
        'message' => 'Erro durante a atualização: ' . $e->getMessage()
    ], 'error');
} finally {
    // Aguarda 1 segundo antes de encerrar a conexão
    sleep(1);
}

// Encerra o script
exit();
?>
