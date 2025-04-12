<?php
// app/views/index.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notícias de Política</title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
    <!-- jQuery (necessário para o DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        thead {
            background-color: #f5f5f5;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        /* Área para exibir os logs de depuração */
        #debug-log {
            background: #f0f0f0;
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 20px;
            max-height: 250px;
            overflow-y: scroll;
            font-family: monospace;
            font-size: 12px;
        }
        /* Área para exibir informações de atualização */
        #update-info {
            margin-bottom: 20px;
            padding: 10px;
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        #force-update-btn {
            margin-bottom: 20px;
            padding: 8px 16px;
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #force-update-btn:hover {
            background: #1565c0;
        }
        /* Indicador de carregamento discreto */
        #loading-indicator {
            display: inline-block;
            margin-left: 10px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            color: #555;
        }
        #update-log {
            font-size: 13px;
            line-height: 1.5;
        }
        #update-log div {
            margin-bottom: 3px;
            border-bottom: 1px dotted #eee;
        }
        .text-primary { color: #1976d2; }
        .text-success { color: #2e7d32; font-weight: bold; }
        .text-info { color: #0277bd; }
        .text-danger { color: #c62828; font-weight: bold; }
    </style>
    <script>
        $(document).ready(function(){
            $('#newsTable').DataTable({
                language: {
                    "decimal":        "",
                    "emptyTable":     "Nenhuma notícia encontrada",
                    "info":           "Mostrando de _START_ até _END_ de _TOTAL_ entradas",
                    "infoEmpty":      "Mostrando 0 até 0 de 0 entradas",
                    "infoFiltered":   "(filtrado de _MAX_ entradas no total)",
                    "thousands":      ".",
                    "lengthMenu":     "Mostrar _MENU_ entradas",
                    "loadingRecords": "Carregando...",
                    "processing":     "Processando...",
                    "search":         "Buscar:",
                    "zeroRecords":    "Nenhum registro encontrado",
                    "paginate": {
                        "first":      "Primeiro",
                        "last":       "Último",
                        "next":       "Próximo",
                        "previous":   "Anterior"
                    },
                    "aria": {
                        "sortAscending":  ": ativar para classificar em ordem crescente",
                        "sortDescending": ": ativar para classificar em ordem decrescente"
                    }
                },
                order: [[0, 'desc']], // Ordena pela coluna de data usando o valor ISO no atributo data-order.
                pageLength: 10
            });
            
            // Ao clicar no botão "Forçar Atualização"
            $('#force-update-btn').click(function(){
                // Desabilita o botão durante a atualização
                $(this).prop('disabled', true);
                
                // Remove log anterior se existir
                $('#update-log').remove();
                
                // Cria um div para exibir o log de atualização
                let $updateLog = $('<div id="update-log" style="margin:10px 0; padding:8px; background:#f8f9fa; border:1px solid #ddd; border-radius:4px; font-family:monospace; max-height:300px; overflow-y:auto;"></div>');
                $('#loading-indicator').html('<span>Conectando ao servidor...</span>').after($updateLog);
                
                // Adiciona uma linha ao log de atualização
                function addLogLine(message, className = '') {
                    let timestamp = new Date().toLocaleTimeString();
                    $updateLog.append(`<div class="${className}">[${timestamp}] ${message}</div>`);
                    // Auto-scroll para o final
                    $updateLog.scrollTop($updateLog[0].scrollHeight);
                }
                
                // Contador para reconexões
                let reconnectAttempts = 0;
                const MAX_RECONNECT_ATTEMPTS = 3;
                
                // Função para criar e configurar a conexão SSE
                function setupEventSource() {
                    // Fecha a conexão existente se houver
                    if (window.activeEventSource) {
                        window.activeEventSource.close();
                    }
                    
                    // Adiciona um timestamp para evitar cache
                    const eventSource = new EventSource('/api/force_update.php?t=' + Date.now());
                    window.activeEventSource = eventSource;
                    
                    // Evento de abertura da conexão
                    eventSource.onopen = function() {
                        addLogLine("Conexão estabelecida com o servidor", "text-info");
                        reconnectAttempts = 0; // Reseta contagem de tentativas
                    };
                    
                    // Evento de início de processo
                    eventSource.addEventListener('start', function(e) {
                        const data = JSON.parse(e.data);
                        $('#loading-indicator').html('<span style="color:#ff6600">Atualizando...</span>');
                        addLogLine(data.message, 'text-primary');
                    });
                    
                    // Evento de progresso
                    eventSource.addEventListener('progress', function(e) {
                        const data = JSON.parse(e.data);
                        addLogLine(data.message);
                    });
                    
                    // Evento de heartbeat para manter a conexão
                    eventSource.addEventListener('heartbeat', function() {
                        console.log("Heartbeat recebido");
                    });
                    
                    // Evento de conclusão
                    eventSource.addEventListener('complete', function(e) {
                        const data = JSON.parse(e.data);
                        
                        // Adiciona informação final de sucesso
                        addLogLine(`Concluído em ${data.execution_time}s! Total de ${data.articles_count} notícias.`, 'text-success');
                        
                        // Adiciona estatísticas por fonte
                        if (data.sources) {
                            let sourcesText = 'Notícias por fonte: ';
                            Object.entries(data.sources).forEach(([source, count], index, arr) => {
                                sourcesText += `${source} (${count})${index < arr.length - 1 ? ', ' : ''}`;
                            });
                            addLogLine(sourcesText, 'text-info');
                        }
                        
                        // Exibe mensagem de sucesso no indicador principal
                        $('#loading-indicator').html(`<span style="color:#228B22">Atualização concluída!</span>`);
                        
                        // Fecha a conexão SSE
                        eventSource.close();
                        window.activeEventSource = null;
                        
                        // Reativa o botão após 1 segundo
                        setTimeout(function(){
                            $('#force-update-btn').prop('disabled', false);
                        }, 1000);
                        
                        // Recarrega a página após 5 segundos
                        setTimeout(function(){
                            location.reload();
                        }, 5000);
                    });
                    
                    // Evento de erro no processo
                    eventSource.addEventListener('error', function(e) {
                        if (e.data) {
                            try {
                                const data = JSON.parse(e.data);
                                addLogLine(data.message, 'text-danger');
                            } catch (err) {
                                addLogLine("Erro no processamento: " + e.data, 'text-danger');
                            }
                        }
                    });
                    
                    // Em caso de erro na conexão
                    eventSource.onerror = function(e) {
                        // Tenta reconectar algumas vezes
                        if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                            reconnectAttempts++;
                            addLogLine(`Tentativa de reconexão ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS}...`, 'text-warning');
                            
                            setTimeout(function() {
                                eventSource.close();
                                setupEventSource();
                            }, 2000); // Espera 2 segundos antes de reconectar
                        } else {
                            addLogLine("Conexão com o servidor perdida após várias tentativas", 'text-danger');
                            $('#loading-indicator').html(`<span style="color:#d32f2f">Erro de conexão</span>`);
                            
                            eventSource.close();
                            window.activeEventSource = null;
                            $('#force-update-btn').prop('disabled', false);
                        }
                    };
                    
                    return eventSource;
                }
                
                // Inicia a conexão SSE
                setupEventSource();
            });
        });
    </script>
</head>
<body>
    <h1>Notícias de Política</h1>
    <?php 
        require_once __DIR__ . '/../../config/config.php';
        $cacheFile = CACHE_DIR . '/all_news.json';
        $cacheTime = 600; // 10 minutos
        if (file_exists($cacheFile)) {
            $lastUpdate = filemtime($cacheFile);
            $nextUpdate = $lastUpdate + $cacheTime;
            echo "<div id='update-info'>";
            echo "<strong>Última atualização:</strong> " . date("d/m/Y H:i:s", $lastUpdate) . "<br>";
            echo "<strong>Próxima atualização (estimada):</strong> " . date("d/m/Y H:i:s", $nextUpdate);
            echo "</div>";
        } else {
            echo "<div id='update-info'><strong>Nenhuma atualização realizada.</strong></div>";
        }
    ?>
    
    <!-- Botão para forçar atualização com indicador discreto -->
    <button id="force-update-btn">Forçar Atualização</button>
    <span id="loading-indicator"></span>
    
    <?php if (isset($news) && is_array($news) && count($news) > 0): ?>
        <table id="newsTable">
            <thead>
                <tr>
                    <th>Publicado em</th>
                    <th>Veículo</th>
                    <th>Título</th>
                    <th>Descrição</th>
                    <th>Autor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($news as $item): ?>
                    <tr>
                        <td data-order="<?php echo $item['publishedAt'] ?: ''; ?>">
                            <?php 
                                if (!empty($item['publishedAt']) && $item['publishedAt'] !== "1970-01-01T00:00:00+00:00" && strtotime($item['publishedAt']) !== false) {
                                    echo date("d/m/Y H:i", strtotime($item['publishedAt']));
                                } else {
                                    echo 'Data não informada.';
                                }
                            ?>
                        </td>
                        <td><?php echo $item['source'] ?: 'Fonte não informada.'; ?></td>
                        <td>
                            <a href="<?php echo $item['url']; ?>" target="_blank">
                                <?php echo $item['title'] ?? 'Sem título'; ?>
                            </a>
                        </td>
                        <td><?php echo $item['description'] ?: 'Descrição não disponível.'; ?></td>
                        <td><?php echo $item['author'] ?: 'Autor não disponível.'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhuma notícia encontrada.</p>
    <?php endif; ?>

    <!-- Área para exibir os logs de depuração com melhorias visuais -->
    <div id="debug-container">
        <div id="debug-header">
            <h3>Debug Logs</h3>
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
                $logCount = count($lines);
                
                // Limita a exibição aos 100 logs mais recentes para melhor desempenho
                $displayLines = array_slice($lines, 0, 100);
                
                foreach ($displayLines as $line) {
                    // Processa a linha para extrair nível, data e mensagem
                    $logLevel = 'INFO'; // Nível padrão
                    
                    if (preg_match('/\[(.*?)\]\[(INFO|WARNING|ERROR|DEBUG)\](.*)/', $line, $matches)) {
                        $timestamp = $matches[1];
                        $logLevel = $matches[2];
                        $logContent = $matches[3];
                    } else {
                        $timestamp = '';
                        $logContent = $line;
                    }
                    
                    // Define a classe CSS baseada no nível do log
                    $logClass = 'log-' . strtolower($logLevel);
                    
                    echo "<div class='log-entry $logClass' data-level='$logLevel'>";
                    echo "<span class='log-timestamp'>$timestamp</span>";
                    echo "<span class='log-level'>$logLevel</span>";
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

    <!-- Estilos CSS para a área de debug -->
    <style>
        #debug-container {
            margin-top: 30px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        
        #debug-header {
            background: #f1f1f1;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }
        
        #debug-header h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .debug-filters {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .debug-filters label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            user-select: none;
        }
        
        .debug-search {
            display: flex;
            gap: 5px;
            margin-top: 10px;
            width: 100%;
        }
        
        #log-search {
            flex: 1;
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        
        button#search-btn, button#clear-logs-btn {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 5px 10px;
            cursor: pointer;
        }
        
        button#clear-logs-btn {
            background: #dc3545;
        }
        
        #debug-log {
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .log-container {
            padding: 10px 0;
        }
        
        .log-entry {
            display: flex;
            padding: 2px 10px;
            margin-bottom: 1px;
            border-left: 4px solid transparent;
        }
        
        .log-entry:hover {
            background-color: rgba(0,0,0,0.05);
        }
        
        .log-timestamp {
            min-width: 160px;
            color: #666;
        }
        
        .log-level {
            min-width: 80px;
            font-weight: bold;
        }
        
        .log-message {
            flex: 1;
        }
        
        /* Cores para os diferentes níveis de log */
        .log-info {
            border-left-color: #0d6efd;
        }
        .log-info .log-level {
            color: #0d6efd;
        }
        
        .log-warning {
            border-left-color: #ffc107;
            background-color: rgba(255, 243, 205, 0.3);
        }
        .log-warning .log-level {
            color: #856404;
        }
        
        .log-error {
            border-left-color: #dc3545;
            background-color: rgba(248, 215, 218, 0.3);
        }
        .log-error .log-level {
            color: #721c24;
        }
        
        .log-debug {
            border-left-color: #6c757d;
        }
        .log-debug .log-level {
            color: #6c757d;
        }
        
        .log-more {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 5px;
        }
        
        .log-empty {
            padding: 20px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
        
        /* Para destacar resultados de busca */
        .log-highlight {
            background-color: #ffeb3b;
            color: #000;
        }
        
        /* Log oculto por filtro */
        .log-hidden {
            display: none;
        }
    </style>

    <!-- JavaScript para interatividade da área de debug -->
    <script>
        $(document).ready(function() {
            // Contador de logs inicial
            updateLogCount();
            
            // Filtrar logs por nível
            $('.log-filter').change(function() {
                let activeFilters = [];
                $('.log-filter:checked').each(function() {
                    activeFilters.push($(this).val());
                });
                
                $('.log-entry').each(function() {
                    const logLevel = $(this).data('level');
                    if (!logLevel || activeFilters.includes(logLevel)) {
                        $(this).removeClass('log-hidden');
                    } else {
                        $(this).addClass('log-hidden');
                    }
                });
                
                updateLogCount();
            });
            
            // Busca nos logs
            $('#search-btn').click(function() {
                searchLogs();
            });
            
            $('#log-search').keypress(function(e) {
                if (e.which == 13) { // Enter key
                    searchLogs();
                }
            });
            
            // Limpar visualização
            $('#clear-logs-btn').click(function() {
                $('.log-container').empty()
                    .append('<div class="log-empty">Logs limpos da visualização.</div>');
                updateLogCount();
            });
            
            function searchLogs() {
                const searchText = $('#log-search').val().toLowerCase();
                
                // Remove destacamento anterior
                $('.log-message').find('mark').contents().unwrap();
                
                if (!searchText) {
                    return;
                }
                
                let matchCount = 0;
                
                // Para cada entrada de log visível
                $('.log-entry:not(.log-hidden)').each(function() {
                    const $message = $(this).find('.log-message');
                    const messageText = $message.text();
                    
                    if (messageText.toLowerCase().includes(searchText)) {
                        // Destaca o texto encontrado
                        const regex = new RegExp('(' + searchText.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + ')', 'gi');
                        const highlighted = messageText.replace(regex, '<mark>$1</mark>');
                        $message.html(highlighted);
                        matchCount++;
                    }
                });
                
                // Feedback da busca
                if (matchCount > 0) {
                    alert(`Encontradas ${matchCount} ocorrências para "${searchText}"`);
                } else {
                    alert(`Nenhuma ocorrência encontrada para "${searchText}"`);
                }
            }
            
            function updateLogCount() {
                const totalLogs = $('.log-entry').length;
                const visibleLogs = $('.log-entry:not(.log-hidden)').length;
                
                if (totalLogs === visibleLogs) {
                    $('#log-count').text(`${totalLogs} logs`);
                } else {
                    $('#log-count').text(`${visibleLogs} de ${totalLogs} logs`);
                }
            }
        });
    </script>
</body>
</html>
