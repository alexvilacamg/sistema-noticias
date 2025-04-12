/**
 * Funções específicas para a página de administração
 */
$(document).ready(function() {
    // Alternância entre diferentes painéis (se necessário)
    $('.admin-nav-item').click(function() {
        const target = $(this).data('target');
        
        // Ativa o item de navegação atual
        $('.admin-nav-item').removeClass('active');
        $(this).addClass('active');
        
        // Mostra o painel correspondente
        $('.admin-panel').hide();
        $(target).show();
    });
    
    // Atualização de estatísticas em tempo real (opcional)
    function refreshStats() {
        $.getJSON('/api/admin-stats.php')
            .done(function(data) {
                if (data.success) {
                    $('#total-news').text(data.stats.totalNews);
                    $('#total-sources').text(data.stats.totalSources);
                    $('#last-update').text(data.stats.lastUpdate);
                }
            });
    }
    
    // Opcional: atualizar estatísticas a cada 60 segundos
    // setInterval(refreshStats, 60000);
});