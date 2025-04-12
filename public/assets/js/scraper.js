/**
 * Lógica para atualização de notícias via Server-Sent Events
 */
$(document).ready(function() {
    // Ao clicar no botão "Forçar Atualização"
    $('#force-update-btn').click(function() {
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
                setTimeout(function() {
                    $('#force-update-btn').prop('disabled', false);
                }, 1000);
                
                // Recarrega a página após 5 segundos
                setTimeout(function() {
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