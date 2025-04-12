/**
 * Implementação de busca universal para cartões e tabela
 */
$(document).ready(function() {
    const $searchInput = $('#universal-search');
    const $clearButton = $('#clear-search');
    const $newsCards = $('.news-card');
    const dataTable = $('#newsTable').DataTable();
    
    // Função para sincronizar a busca com o DataTable quando estivermos no modo tabela
    function syncWithDataTable(searchTerm) {
        if ($('#table-view').is(':visible')) {
            dataTable.search(searchTerm).draw();
        }
    }
    
    // Função para limpar o campo de busca
    function clearSearch() {
        $searchInput.val('');
        $searchInput.trigger('input');
        $clearButton.hide();
    }
    
    // Mostrar/ocultar o botão limpar
    $searchInput.on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        
        // Mostrar/ocultar botão de limpar
        if (searchTerm.length > 0) {
            $clearButton.show();
        } else {
            $clearButton.hide();
        }
        
        // Filtrar cartões no modo grid
        if ($('#grid-view').is(':visible')) {
            filterCards(searchTerm);
        }
        
        // Sincronizar com DataTable no modo tabela
        syncWithDataTable(searchTerm);
    });
    
    // Função para filtrar cartões
    function filterCards(searchTerm) {
        if (!searchTerm) {
            // Se não houver termo de busca, mostra todos os cartões
            // (respeitando o filtro de fonte atual)
            $('.source-filter.active').trigger('click');
            return;
        }
        
        $newsCards.each(function() {
            const $card = $(this);
            const title = $card.find('.news-title').text().toLowerCase();
            const description = $card.find('.news-description').text().toLowerCase();
            const author = $card.find('.news-author').text().toLowerCase();
            const source = $card.find('.news-source').text().toLowerCase();
            
            // Verifica se o termo de busca existe em qualquer campo do cartão
            const matches = title.includes(searchTerm) || 
                           description.includes(searchTerm) || 
                           author.includes(searchTerm) || 
                           source.includes(searchTerm);
            
            // Mostra ou oculta com base no resultado da busca
            if (matches) {
                $card.show();
            } else {
                $card.hide();
            }
        });
        
        // Mostra mensagem se não houver resultados
        const visibleCards = $newsCards.filter(':visible').length;
        if (visibleCards === 0) {
            if ($('#no-results').length === 0) {
                $('#grid-view').append('<div id="no-results" class="no-results">Nenhum resultado encontrado para "' + searchTerm + '"</div>');
            }
        } else {
            $('#no-results').remove();
        }
    }
    
    // Limpar busca ao clicar no botão
    $clearButton.on('click', clearSearch);
    
    // Ao alternar entre visualizações, sincroniza o campo de busca
    $('#grid-view-btn, #table-view-btn').on('click', function() {
        const searchTerm = $searchInput.val();
        if (searchTerm) {
            setTimeout(function() {
                if ($('#grid-view').is(':visible')) {
                    filterCards(searchTerm.toLowerCase().trim());
                } else {
                    syncWithDataTable(searchTerm);
                }
            }, 10);
        }
    });
    
    // Quando aplicamos um filtro de fonte, respeitamos também o termo de busca
    $('.source-filter').on('click', function() {
        const searchTerm = $searchInput.val().toLowerCase().trim();
        if (searchTerm && $('#grid-view').is(':visible')) {
            setTimeout(function() {
                filterCards(searchTerm);
            }, 10);
        }
    });
});