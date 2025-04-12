<?php
date_default_timezone_set('America/Sao_Paulo');
define('CACHE_DIR', __DIR__ . '/../cache');
define('LOG_DIR', __DIR__ . '/../logs');
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}
define('LOG_FILE', LOG_DIR . '/debug.log');

// Define constantes para níveis de log apenas se não estiverem definidas
if (!defined('LOG_INFO'))    define('LOG_INFO', 'INFO');
if (!defined('LOG_WARNING')) define('LOG_WARNING', 'WARNING');
if (!defined('LOG_ERROR'))   define('LOG_ERROR', 'ERROR');
if (!defined('LOG_DEBUG'))   define('LOG_DEBUG', 'DEBUG');

/**
 * Função aprimorada de log com suporte a níveis
 * 
 * @param string $message Mensagem a ser registrada
 * @param string $level Nível do log (INFO, WARNING, ERROR, DEBUG)
 * @param string $context Contexto opcional para o log
 * @return bool Sucesso da operação
 */
function debug_log($message, $level = 'INFO', $context = '') {
    $date = date("Y-m-d H:i:s");
    $contextInfo = $context ? "[$context]" : "";
    // Formato corrigido: [DATA][NÍVEL]mensagem (sem quebras de linha no nível)
    $entry = "[$date][$level]$contextInfo $message" . PHP_EOL;
    return file_put_contents(LOG_FILE, $entry, FILE_APPEND);
}

/**
 * Funções auxiliares para simplificar os logs em diferentes níveis
 * Cada função é definida apenas se não existir
 */
if (!function_exists('log_info')) {
    function log_info($message, $context = '') {
        return debug_log($message, LOG_INFO, $context);
    }
}

if (!function_exists('log_warning')) {
    function log_warning($message, $context = '') {
        return debug_log($message, LOG_WARNING, $context);
    }
}

if (!function_exists('log_error')) {
    function log_error($message, $context = '') {
        return debug_log($message, LOG_ERROR, $context);
    }
}

if (!function_exists('log_debug')) {
    function log_debug($message, $context = '') {
        return debug_log($message, LOG_DEBUG, $context);
    }
}

/**
 * Configurações de Cache - SUBSTITUA COM SUAS CREDENCIAIS
 */
define('CACHE_TYPE', 'file'); // 'redis', 'memcached', ou 'file'
define('CACHE_PREFIX', 'news_');
define('CACHE_TTL', 600); // 10 minutos

// Configurações Redis - SUBSTITUA COM SUAS CREDENCIAIS
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASSWORD', null); // Altere para sua senha
define('REDIS_DATABASE', 0);

// Configurações Memcached - SUBSTITUA COM SUAS CREDENCIAIS
define('MEMCACHED_HOST', '127.0.0.1');
define('MEMCACHED_PORT', 11211);

/**
 * Função para obter a instância de cache
 * 
 * @return App\Cache\CacheInterface
 */
function getCache()
{
    static $cache = null;
    
    if ($cache === null) {
        $config = [
            'prefix' => CACHE_PREFIX,
            'ttl' => CACHE_TTL,
            'directory' => CACHE_DIR . '/data/',
        ];
        
        // Adiciona configurações específicas dependendo do tipo
        switch (CACHE_TYPE) {
            case 'redis':
                $config['host'] = REDIS_HOST;
                $config['port'] = REDIS_PORT;
                $config['password'] = REDIS_PASSWORD;
                $config['database'] = REDIS_DATABASE;
                break;
                
            case 'memcached':
                $config['servers'] = [
                    [
                        'host' => MEMCACHED_HOST,
                        'port' => MEMCACHED_PORT,
                        'weight' => 100
                    ]
                ];
                break;
        }
        
        // Utiliza a fábrica para criar a implementação
        $cache = \App\Cache\CacheFactory::create(CACHE_TYPE, $config);
    }
    
    return $cache;
}
?>
