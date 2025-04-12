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
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
        /* Estilos específicos para a área de log */
        .log-context {
            min-width: 120px;
            color: #0277bd;
            font-weight: bold;
            margin-right: 10px;
        }

        .log-level {
            min-width: 70px;
            padding: 2px 6px;
            border-radius: 4px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-right: 10px;
            color: white !important; /* Forçar branco para todos os modos */
        }

        /* Estilos corretos para os níveis de log com cores de fundo */
        .log-info .log-level {
            background-color: #3182ce;
            color: white !important;
        }

        .log-warning .log-level {
            background-color: #dd6b20;
            color: white !important;
        }

        .log-error .log-level {
            background-color: #e53e3e;
            color: white !important;
        }

        .log-debug .log-level {
            background-color: #718096;
            color: white !important;
        }

        /* Adicione ao <head> ou ao seu arquivo CSS */
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        h1 {
            font-size: 2.2rem;
            color: #2a2a72;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e7e7e7;
            padding-bottom: 0.5rem;
        }

        /* Tornar a interface responsiva */
        @media (max-width: 768px) {
            body { padding: 10px; }
            h1 { font-size: 1.8rem; }
            #newsTable th, #newsTable td { padding: 8px 5px; }
        }

        /* Estilos para os cards de notícias */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .news-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px;
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
            animation: fadeIn 0.5s ease-in-out;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .news-source {
            position: absolute;
            top: -10px;
            right: 10px;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .g1 { background-color: #c4170c; }
        .uol { background-color: #1e4b9b; }
        .folha { background-color: #2661a8; }

        .news-title {
            font-size: 18px;
            margin-top: 10px;
            margin-bottom: 8px;
        }

        .news-title a {
            color: #333;
            text-decoration: none;
        }

        .news-title a:hover {
            color: #0066cc;
        }

        .news-description {
            color: #666;
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-meta {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }

        /* Estilos para o modo escuro */
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }

        body.dark-mode h1 {
            color: #bb86fc;
            border-bottom-color: #333;
        }

        body.dark-mode .news-card {
            background: #1e1e1e;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }

        body.dark-mode .news-title a {
            color: #e0e0e0;
        }

        body.dark-mode .news-description {
            color: #aaa;
        }

        /* Botão de toggle */
        .dark-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f0f0f0;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background 0.3s;
        }

        body.dark-mode .dark-mode-toggle {
            background: #333;
            color: #bb86fc;
        }

        /* Estilo para o painel de atualização */
        .update-info {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 0;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        body.dark-mode .update-info {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: #e2e8f0;
            border: 1px solid #4a5568;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .update-info-icon {
            background-color: #4299e1;
            color: white;
            font-size: 24px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.dark-mode .update-info-icon {
            background-color: #4a5568;
            color: #e2e8f0;
        }

        .update-info-content {
            padding: 15px 20px;
            flex-grow: 1;
        }

        body.dark-mode .update-info-content {
            color: #e2e8f0;
        }

        .update-data {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .update-data .label {
            font-size: 12px;
            color: #718096;
            display: block;
        }

        body.dark-mode .update-data .label {
            color: #a0aec0;
        }

        .update-data .value {
            font-size: 14px;
            font-weight: bold;
            color: #2d3748;
            display: block;
        }

        body.dark-mode .update-data .value {
            color: #e2e8f0;
        }

        /* Novo estilo para o botão de atualização */
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 15px;
        }

        .btn-primary {
            background: #4299e1;
            color: white;
        }

        .btn-primary:hover {
            background: #3182ce;
            box-shadow: 0 4px 8px rgba(49, 130, 206, 0.2);
        }

        body.dark-mode .btn-primary {
            background: #4a5568;
        }

        body.dark-mode .btn-primary:hover {
            background: #2d3748;
        }

        #force-update-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        #force-update-btn:disabled i {
            animation: rotating 1.5s linear infinite;
        }

        @keyframes rotating {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Melhorias para a área de debug */
        #debug-container {
            margin-top: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        body.dark-mode #debug-container {
            background: #1e1e1e;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        #debug-header {
            background: #f1f1f1;
            padding: 15px;
            border-bottom: 1px solid #e1e1e1;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between;
            align-items: center;
        }

        body.dark-mode #debug-header {
            background: #2d2d2d;
            border-color: #444;
        }

        #debug-header h3 {
            margin: 0;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .debug-filters {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            background: rgba(0,0,0,0.03);
            padding: 8px 12px;
            border-radius: 5px;
        }

        body.dark-mode .debug-filters {
            background: rgba(255,255,255,0.05);
        }

        .debug-filters label {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s;
        }

        .debug-filters label:hover {
            background: rgba(0,0,0,0.05);
        }

        body.dark-mode .debug-filters label:hover {
            background: rgba(255,255,255,0.1);
        }

        .debug-search {
            display: flex;
            gap: 5px;
            margin-top: 10px;
            width: 100%;
        }

        #log-search {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        body.dark-mode #log-search {
            background: #2d2d2d;
            border-color: #444;
            color: #e0e0e0;
        }

        button#search-btn, button#clear-logs-btn {
            background: #718096;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 15px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }

        button#search-btn:hover, button#clear-logs-btn:hover {
            background: #4a5568;
        }

        button#clear-logs-btn {
            background: #e53e3e;
        }

        button#clear-logs-btn:hover {
            background: #c53030;
        }

        /* Melhorias para as entradas de log */
        .log-entry {
            padding: 8px 15px;
            margin-bottom: 2px;
            border-left: 4px solid transparent;
            display: flex;
            align-items: center;
            transition: background 0.2s;
        }

        .log-entry:hover {
            background-color: rgba(0,0,0,0.03);
        }

        body.dark-mode .log-entry:hover {
            background-color: rgba(255,255,255,0.03);
        }

        .log-timestamp {
            min-width: 160px;
            color: #718096;
            font-size: 12px;
        }

        body.dark-mode .log-timestamp {
            color: #a0aec0;
        }

        .source-filters {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }

        .filter-title {
            font-weight: bold;
            color: #718096;
        }

        .source-filter {
            padding: 5px 15px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .source-filter:hover, .source-filter.active {
            background: #4299e1;
            color: white;
            border-color: #4299e1;
        }

        .source-filter[data-source="g1"].active {
            background: #c4170c;
            border-color: #c4170c;
        }

        .source-filter[data-source="uol"].active {
            background: #1e4b9b;
            border-color: #1e4b9b;
        }

        .source-filter[data-source="folha"].active {
            background: #2661a8;
            border-color: #2661a8;
        }

        body.dark-mode .source-filter {
            background: #2d3748;
            border-color: #4a5568;
            color: #e2e8f0;
        }

        /* Ajustar toda a área de logs para o modo escuro */
        body.dark-mode #debug-log {
            background-color: #1e1e1e; /* Fundo escuro */
        }

        body.dark-mode .log-container {
            background-color: #1e1e1e;
        }

        body.dark-mode .log-entry {
            background-color: #2d2d2d; /* Um pouco mais claro que o fundo */
            border-left-color: inherit; /* Manter cores de borda */
            color: #e0e0e0; /* Texto claro para contraste */
        }

        body.dark-mode .log-entry:hover {
            background-color: #3a3a3a; /* Destacar no hover */
        }

        /* Cores de borda específicas por nível no modo escuro */
        body.dark-mode .log-info {
            border-left-color: #4299e1; /* Azul mais brilhante */
        }

        body.dark-mode .log-warning {
            border-left-color: #ed8936; /* Laranja mais brilhante */
            background-color: rgba(237, 137, 54, 0.1); /* Fundo sutilmente colorido */
        }

        body.dark-mode .log-error {
            border-left-color: #f56565; /* Vermelho mais brilhante */
            background-color: rgba(245, 101, 101, 0.1); /* Fundo sutilmente colorido */
        }

        body.dark-mode .log-debug {
            border-left-color: #a0aec0;
        }

        /* Ajustar cores do texto no modo escuro */
        body.dark-mode .log-timestamp {
            color: #a0aec0; /* Cinza azulado claro */
        }

        body.dark-mode .log-message {
            color: #e2e8f0; /* Quase branco */
        }

        body.dark-mode .log-context {
            color: #63b3ed; /* Azul claro */
        }

        body.dark-mode .log-more {
            color: #a0aec0;
        }

        /* Correção específica para o contraste no modo escuro da seção de atualização */
        html body.dark-mode .update-info {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%) !important;
            color: #e2e8f0 !important;
        }

        html body.dark-mode .update-data .label,
        html body.dark-mode .update-data .value,
        html body.dark-mode .update-info-content {
            color: #e2e8f0 !important;
        }

        html body.dark-mode .update-empty {
            color: #e2e8f0 !important;
        }
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

            // Script para filtrar notícias por fonte
            $('.source-filter').click(function() {
                const source = $(this).data('source');
                
                // Atualiza classes ativas
                $('.source-filter').removeClass('active');
                $(this).addClass('active');
                
                if (source === 'all') {
                    $('.news-card').show();
                } else {
                    $('.news-card').hide();
                    $(`.news-card .news-source.${source}`).parent().show();
                }
            });
        });

        // Adicione este script para o toggle do modo escuro
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
            
            // Verifica preferência salva ou sistema
            const currentTheme = localStorage.getItem('theme');
            if (currentTheme === 'dark' || (!currentTheme && prefersDarkScheme.matches)) {
                document.body.classList.add('dark-mode');
            }
            
            darkModeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                
                // Salva preferência
                if (document.body.classList.contains('dark-mode')) {
                    localStorage.setItem('theme', 'dark');
                } else {
                    localStorage.setItem('theme', 'light');
                }
            });
        });
    </script>
</head>
<body>
    <!-- Adicione este botão no topo da página -->
    <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Alternar modo escuro">
        <i class="fas fa-moon"></i>
    </button>

    <h1>Notícias de Política</h1>
    <?php 
        require_once __DIR__ . '/../../config/config.php';
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
    
    <!-- Adicione antes da tabela de notícias -->
    <div class="source-filters">
        <span class="filter-title">Filtrar por fonte:</span>
        <button class="source-filter active" data-source="all">Todas</button>
        <button class="source-filter" data-source="g1">G1</button>
        <button class="source-filter" data-source="uol">UOL</button>
        <button class="source-filter" data-source="folha">Folha</button>
    </div>

    <?php if (isset($news) && is_array($news) && count($news) > 0): ?>
        <!-- Substitua a tabela existente por esta estrutura de cards -->
        <div class="news-grid">
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
    <?php else: ?>
        <p>Nenhuma notícia encontrada.</p>
    <?php endif; ?>

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
                $logCount = count($lines);
                
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
</body>
</html>
