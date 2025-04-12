<?php
// app/views/index.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notícias de Política</title>
    
    <!-- CSS externos -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <!-- CSS do sistema -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/cards.css">
    <link rel="stylesheet" href="/assets/css/table.css">
    <link rel="stylesheet" href="/assets/css/logs.css">
    <link rel="stylesheet" href="/assets/css/dark-mode.css">
    
    <!-- jQuery e DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    
    <!-- JavaScript do sistema -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/dark-mode.js"></script>
    <script src="/assets/js/view-switcher.js"></script>
    <script src="/assets/js/scraper.js"></script>
    <script src="/assets/js/logs.js"></script>
</head>
<body>
    <!-- Adicione este botão no topo da página -->
    <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Alternar modo escuro">
        <i class="fas fa-moon"></i>
    </button>

    <h1>Notícias de Política</h1>
    <?php 
        require_once __DIR__ . '/../../../config/config.php';
        $cacheFile = CACHE_DIR . '/all_news.json';
        $cacheTime = 600; // 10 minutos
    ?>
    <!-- Substitua o div #update-info existente por este -->
    <div id="update-info" class="update-info">
        <div class="update-info-icon">
            <i class="fas fa-sync-alt"></i>
        </div>
        <div class="update-info-content">
            <?php if (file_exists($cacheFile)): ?>
                <div class="update-data">
                    <div>
                        <span class="label">Última atualização:</span>
                        <span class="value"><?php echo date("d/m/Y H:i:s", filemtime($cacheFile)); ?></span>
                    </div>
                    <div>
                        <span class="label">Próxima atualização:</span>
                        <span class="value"><?php echo date("d/m/Y H:i:s", filemtime($cacheFile) + $cacheTime); ?></span>
                    </div>
                    <div>
                        <span class="label">Total de notícias:</span>
                        <span class="value"><?php echo count($news); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="update-empty">Nenhuma atualização realizada</div>
            <?php endif; ?>
        </div>
        <button id="force-update-btn" class="btn btn-primary">
            <i class="fas fa-sync-alt"></i> Forçar Atualização
        </button>
    </div>
    
    <!-- Agrupar filtros e controles de visualização -->
    <div class="controls-container">
        <div class="source-filters">
            <span class="filter-title">Filtrar por fonte:</span>
            <button class="source-filter active" data-source="all">Todas</button>
            <button class="source-filter" data-source="g1">G1</button>
            <button class="source-filter" data-source="uol">UOL</button>
            <button class="source-filter" data-source="folha">Folha</button>
        </div>
        <div class="view-controls">
            <span class="filter-title">Visualização:</span>
            <button id="grid-view-btn" class="view-btn active" title="Visualização em cards">
                <i class="fas fa-th-large"></i>
            </button>
            <button id="table-view-btn" class="view-btn" title="Visualização em tabela">
                <i class="fas fa-table"></i>
            </button>
        </div>
    </div>

    <!-- Substitua o bloco onde mostra as notícias por: -->
    <?php if (isset($news) && is_array($news) && count($news) > 0): ?>
        <!-- Visualização em cards (padrão) -->
        <div class="news-grid" id="grid-view">
            <?php foreach ($news as $item): ?>
                <div class="news-card">
                    <span class="news-source <?php echo strtolower($item['source']); ?>">
                        <?php echo $item['source']; ?>
                    </span>
                    <h3 class="news-title">
                        <a href="<?php echo $item['url']; ?>" target="_blank">
                            <?php echo $item['title'] ?? 'Sem título'; ?>
                        </a>
                    </h3>
                    <p class="news-description"><?php echo $item['description'] ?: 'Descrição não disponível.'; ?></p>
                    <div class="news-meta">
                        <span class="news-date">
                            <?php 
                            if (!empty($item['publishedAt']) && $item['publishedAt'] !== "1970-01-01T00:00:00+00:00") {
                                echo '<i class="fas fa-calendar-alt"></i> ' . date("d/m/Y H:i", strtotime($item['publishedAt']));
                            }
                            ?>
                        </span>
                        <span class="news-author">
                            <?php if ($item['author'] && $item['author'] !== 'Não disponível'): ?>
                                <i class="fas fa-user"></i> <?php echo $item['author']; ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Visualização em tabela (inicialmente oculta) -->
        <div id="table-view" style="display: none;">
            <table id="newsTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Título</th>
                        <th>Fonte</th>
                        <th>Autor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $item): ?>
                    <tr>
                        <td data-order="<?php echo $item['publishedAt'] ?? ''; ?>">
                            <?php 
                            if (!empty($item['publishedAt']) && $item['publishedAt'] !== "1970-01-01T00:00:00+00:00") {
                                echo date("d/m/Y H:i", strtotime($item['publishedAt']));
                            } else {
                                echo "Data não disponível";
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo $item['url']; ?>" target="_blank" class="news-link">
                                <?php echo $item['title'] ?? 'Sem título'; ?>
                            </a>
                            <?php if (!empty($item['description'])): ?>
                            <div class="description-preview">
                                <?php echo $item['description']; ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="source-badge <?php echo strtolower($item['source']); ?>">
                                <?php echo $item['source']; ?>
                            </span>
                        </td>
                        <td><?php echo $item['author'] ?? 'Não disponível'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Nenhuma notícia encontrada.</p>
    <?php endif; ?>

    <!-- Seção de administração de cache -->
    <div class="admin-section">
        <h3>Gerenciamento de Cache</h3>
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
            <button id="clear-cache-btn" class="btn btn-warning">
                <i class="fas fa-trash-alt"></i> Limpar Cache
            </button>
            <button id="warm-cache-btn" class="btn btn-info">
                <i class="fas fa-fire"></i> Pré-aquecer Cache
            </button>
        </div>
    </div>

    <!-- Área para exibir os logs de depuração com melhorias visuais -->
    <div id="debug-container">
        <div id="debug-header">
            <h3>Log do Sistema</h3>
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
                
                // Limita a exibição aos 100 logs mais recentes para melhor desempenho
                $displayLines = array_slice($lines, 0, 100);
                
                foreach ($displayLines as $line) {
                    // Temos um problema com o formato atual dos logs, vamos tentar extrair as partes importantes
                    
                    // Padrão esperado: [DATA][NÍVEL][CONTEXTO] Mensagem
                    if (preg_match('/\[([\d\- :]+)\]\[(INFO|WARNING|ERROR|DEBUG)\](?:\[(.*?)\])?\s*(.*)/', $line, $matches)) {
                        $timestamp = $matches[1];
                        $logLevel = $matches[2];
                        $context = !empty($matches[3]) ? $matches[3] : '';
                        $logContent = $matches[4];
                    } else {
                        // Fallback para logs antigos ou com formato diferente
                        // Tenta encontrar pelo menos a data e alguma indicação de nível
                        if (preg_match('/\[([\d\- :]+)\]/', $line, $dateMatch)) {
                            $timestamp = $dateMatch[1];
                            
                            // Identifica o nível com base em palavras-chave comuns
                            if (stripos($line, 'erro') !== false || stripos($line, 'falha') !== false) {
                                $logLevel = 'ERROR';
                            } else if (stripos($line, 'aviso') !== false || stripos($line, 'alerta') !== false) {
                                $logLevel = 'WARNING';
                            } else if (stripos($line, 'debug') !== false) {
                                $logLevel = 'DEBUG';
                            } else {
                                $logLevel = 'INFO';
                            }
                            
                            // Extrai o contexto (geralmente entre colchetes após a data)
                            if (preg_match('/\]\[(.*?)\]/', $line, $contextMatch)) {
                                $context = $contextMatch[1];
                            } else {
                                $context = '';
                            }
                            
                            // A mensagem é o resto da linha após os metadados
                            $logContent = preg_replace('/^\[.*?\](\[.*?\])*\s*/', '', $line);
                        } else {
                            // Se não conseguir extrair nada, usa valores padrão
                            $timestamp = '';
                            $logLevel = 'INFO';
                            $context = '';
                            $logContent = $line;
                        }
                    }
                    
                    // Define a classe CSS baseada no nível do log
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

    <!-- Exemplos de uso dos novos logs -->
    <?php
    log_info("Usuário fez login", "Auth");
    log_warning("Múltiplas tentativas de login", "Auth");
    log_error("Falha na conexão com o banco de dados", "Database");
    log_debug("Query executada: SELECT * FROM users", "SQL");

    // Ou continue usando a função genérica com nível personalizado
    debug_log("Operação personalizada", "CUSTOM", "Context");
    ?>
    
    <!-- Adicione este script JavaScript -->
    <script>
    $(document).ready(function() {
        $('#clear-cache-btn').click(function() {
            if (confirm("Tem certeza que deseja limpar todo o cache?")) {
                $.post('/api/cache.php', { action: 'clear' })
                    .done(function(response) {
                        alert(response.message);
                        if (response.success) {
                            location.reload();
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
