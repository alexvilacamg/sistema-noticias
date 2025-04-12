/**
 * Alternância entre visualização em cards e tabela
 */
$(document).ready(function() {
    // Script para filtrar notícias por fonte
    $('.source-filter').click(function() {
        const source = $(this).data('source');
        
        // Atualiza classes ativas
        $('.source-filter').removeClass('active');
        $(this).addClass('active');
        
        // Filtro para os cards
        if (source === 'all') {
            $('.news-card').show();
        } else {
            $('.news-card').hide();
            $(`.news-card .news-source.${source}`).parent().show();
        }
        
        // Filtro para a tabela usando DataTables API
        if (window.newsTable) {
            if (source === 'all') {
                window.newsTable.column(2).search('').draw();
            } else {
                window.newsTable.column(2).search(source, false, false).draw();
            }
        }
        
        // Salva a preferência de filtro
        localStorage.setItem('preferred-filter', source);
    });

    // Restaurar preferência de filtro
    const preferredFilter = localStorage.getItem('preferred-filter');
    if (preferredFilter && preferredFilter !== 'all') {
        $(`.source-filter[data-source="${preferredFilter}"]`).click();
    }

    // Alternar entre visualização em cards e tabela
    $('#grid-view-btn').click(function() {
        $(this).addClass('active');
        $('#table-view-btn').removeClass('active');
        $('#grid-view').show();
        $('#table-view').hide();
        localStorage.setItem('preferred-view', 'grid');
    });

    $('#table-view-btn').click(function() {
        $(this).addClass('active');
        $('#grid-view-btn').removeClass('active');
        $('#table-view').show();
        $('#grid-view').hide();
        // Ajusta as colunas da tabela quando ela se torna visível
        if (window.newsTable) {
            window.newsTable.columns.adjust();
        }
        localStorage.setItem('preferred-view', 'table');
    });

    // Restaurar preferência de visualização
    const preferredView = localStorage.getItem('preferred-view');
    if (preferredView === 'table') {
        $('#table-view-btn').click();
    }
});