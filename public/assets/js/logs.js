/**
 * Funcionalidades da área de logs (filtros, busca, etc.)
 */
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
        
        if (totalLogs === 0) {
            $('#log-count').text('');
            return;
        }
        
        if (totalLogs === visibleLogs) {
            $('#log-count').text(`${totalLogs} logs`);
        } else {
            $('#log-count').text(`${visibleLogs} de ${totalLogs} logs`);
        }
    }
});