<?php
// app/views/admin.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - Sistema de Notícias</title>
    
    <!-- CSS externos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <!-- CSS do sistema -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/logs.css">
    <link rel="stylesheet" href="/assets/css/dark-mode.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- JavaScript do sistema -->
    <script src="/assets/js/dark-mode.js"></script>
    <script src="/assets/js/logs.js"></script>
    <script src="/assets/js/admin.js"></script>
</head>
<body>
    <!-- Botão de modo escuro -->
    <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Alternar modo escuro">
        <i class="fas fa-moon"></i>
    </button>

    <div class="admin-header">
        <h1>Painel Administrativo</h1>
        <a href="/" class="back-link"><i class="fas fa-arrow-left"></i> Voltar para o site</a>
    </div>

    <div class="admin-container">
        <!-- Painel de estatísticas gerais -->
        <div class="admin-panel">
            <h2><i class="fas fa-chart-bar"></i> Estatísticas do Sistema</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value" id="total-news">
                        <?php echo count($news ?? []); ?>
                    </div>
                    <div class="stat-label">Notícias</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="total-sources">
                        <?php 
                            $sources = array_unique(array_column($news ?? [], 'source'));
                            echo count($sources);
                        ?>
                    </div>
                    <div class="stat-label">Fontes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="last-update">
                        <?php
                            $cacheFile = CACHE_DIR . '/all_news.json';
                            echo file_exists($cacheFile) ? date("d/m H:i", filemtime($cacheFile)) : 'N/A';
                        ?>
                    </div>
                    <div class="stat-label">Atualização</div>
                </div>
            </div>
        </div>

        <!-- Seção de administração de cache - Movida da página principal -->
        <div class="admin-panel">
            <h2><i class="fas fa-database"></i> Gerenciamento de Cache</h2>
            <div class="cache-stats">
                <?php
                $stats = \App\Cache\CacheManager::getStats();
                ?>
                <div class="stat-item">
                    <span class="label">Tipo de Cache:</span>
                    <span class="value"><?php echo $stats['type']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">TTL Padrão:</span>
                    <span class="value"><?php echo $stats['ttl']; ?> segundos</span>
                </div>
                <div class="stat-item">
                    <span class="label">Status:</span>
                    <span class="value <?php echo $stats['status'] === 'ativo' ? 'active' : 'inactive'; ?>">
                        <?php echo $stats['status']; ?>
                    </span>
                </div>
            </div>
            
            <div class="cache-actions">
                <button id="clear-cache-btn" class="admin-btn btn-warning">
                    <i class="fas fa-trash-alt"></i> Limpar Cache
                </button>
                <button id="warm-cache-btn" class="admin-btn btn-info">
                    <i class="fas fa-fire"></i> Pré-aquecer Cache
                </button>
            </div>
        </div>

        <!-- Área para exibir os logs de depuração - Movida da página principal -->
        <div id="debug-container" class="admin-panel">
            <div id="debug-header">
                <h2><i class="fas fa-file-alt"></i> Log do Sistema</h2>
                <div class="debug-filters">
                    <label><input type="checkbox" class="log-filter" value="INFO" checked> INFO</label>
                    <label><input type="checkbox" class="log-filter" value="WARNING" checked> WARNING</label>
                    <label><input type="checkbox" class="log-filter" value="ERROR" checked> ERROR</label>
                    <label><input type="checkbox" class="log-filter" value="DEBUG" checked> DEBUG</label>
                    <button id="clear-logs-btn" title="Limpa a visualização dos logs (não apaga o arquivo)">Limpar Visualização</button>
                    <span id="log-count"></span>
                </div>
                <div class="debug-search">
                    <input type="text" id="log-search" placeholder="Buscar nos logs...">
                    <button id="search-btn">Buscar</button>
                </div>
            </div>
            <div id="debug-log">
                <div class="log-container">
                <?php 
                if (defined('LOG_FILE') && file_exists(LOG_FILE)) {
                    $lines = file(LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $lines = array_reverse($lines); // Mais recentes primeiro
                    
                    // Armazena o número total de logs
                    $logCount = count($lines);
                    
                    // Limita a exibição aos 100 logs mais recentes para melhor desempenho
                    $displayLines = array_slice($lines, 0, 100);
                    
                    foreach ($displayLines as $line) {
                        // Extrair os componentes do log: data, nível, contexto, mensagem
                        if (preg_match('/\[([\d\- :]+)\]\[(INFO|WARNING|ERROR|DEBUG)\](?:\[(.*?)\])?\s*(.*)/', $line, $matches)) {
                            $timestamp = $matches[1];
                            $logLevel = $matches[2];
                            $context = !empty($matches[3]) ? $matches[3] : '';
                            $logContent = $matches[4];
                        } else {
                            // Fallback para outros formatos
                            if (preg_match('/\[([\d\- :]+)\]/', $line, $dateMatch)) {
                                $timestamp = $dateMatch[1];
                                
                                if (stripos($line, 'erro') !== false || stripos($line, 'falha') !== false) {
                                    $logLevel = 'ERROR';
                                } else if (stripos($line, 'aviso') !== false || stripos($line, 'alerta') !== false) {
                                    $logLevel = 'WARNING';
                                } else if (stripos($line, 'debug') !== false) {
                                    $logLevel = 'DEBUG';
                                } else {
                                    $logLevel = 'INFO';
                                }
                                
                                if (preg_match('/\]\[(.*?)\]/', $line, $contextMatch)) {
                                    $context = $contextMatch[1];
                                } else {
                                    $context = '';
                                }
                                
                                $logContent = preg_replace('/^\[.*?\](\[.*?\])*\s*/', '', $line);
                            } else {
                                $timestamp = '';
                                $logLevel = 'INFO';
                                $context = '';
                                $logContent = $line;
                            }
                        }
                        
                        $logClass = 'log-' . strtolower($logLevel);
                        
                        echo "<div class='log-entry $logClass' data-level='$logLevel'>";
                        echo "<span class='log-timestamp'>$timestamp</span>";
                        echo "<span class='log-level'>$logLevel</span>";
                        if ($context) {
                            echo "<span class='log-context'>$context</span>";
                        }
                        echo "<span class='log-message'>" . htmlspecialchars($logContent) . "</span>";
                        echo "</div>";
                    }
                    
                    if ($logCount > 100) {
                        echo "<div class='log-entry log-more'>+ " . ($logCount - 100) . " logs adicionais não exibidos (total: $logCount)</div>";
                    }
                } else {
                    echo "<div class='log-empty'>Nenhum log encontrado.</div>";
                }
                ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#clear-cache-btn').click(function() {
            if (confirm("Tem certeza que deseja limpar todo o cache?")) {
                $.post('/api/cache.php', { action: 'clear' })
                    .done(function(response) {
                        alert(response.message);
                        if (response.success) {
                            $('#last-update').text('N/A');
                        }
                    })
                    .fail(function() {
                        alert("Erro de comunicação com o servidor");
                    });
            }
        });
        
        $('#warm-cache-btn').click(function() {
            if (confirm("Iniciar pré-aquecimento do cache?")) {
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');
                
                $.post('/api/cache.php', { action: 'warm' })
                    .done(function(response) {
                        alert(response.message);
                        if (response.success) {
                            location.reload();
                        } else {
                            $('#warm-cache-btn').prop('disabled', false).html('<i class="fas fa-fire"></i> Pré-aquecer Cache');
                        }
                    })
                    .fail(function() {
                        alert("Erro de comunicação com o servidor");
                        $('#warm-cache-btn').prop('disabled', false).html('<i class="fas fa-fire"></i> Pré-aquecer Cache');
                    });
            }
        });
    });
    </script>
</body>
</html>