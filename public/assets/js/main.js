/**
 * Inicialização geral e configurações do DataTables
 */
$(document).ready(function() {
    // Inicialização do DataTables
    const newsTable = $('#newsTable').DataTable({
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
        order: [[0, 'desc']],
        pageLength: 10,
        deferRender: true,
        processing: true,
        searching: true,        // Mantém o recurso de busca ativo
        dom: '<"top">rt<"bottom"lip><"clear">' // Remove o campo de busca padrão ("f") da interface
    });

    // Tornar a variável newsTable acessível globalmente
    window.newsTable = newsTable;
});