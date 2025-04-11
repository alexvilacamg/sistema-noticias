<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notícias de Política</title>
    <link rel="stylesheet" href="/public/css/style.css">
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
                order: [[0, 'desc']], // Ordena pela coluna 0 (Publicado em) de forma decrescente
                pageLength: 10
            });
        });
    </script>
</head>
<body>
    <h1>Notícias de Política</h1>
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
                        <!-- A célula da data inclui um atributo data-order com a data em formato ISO -->
                        <td data-order="<?php echo $item['publishedAt'] ?: ''; ?>">
                            <?php 
                                if (!empty($item['publishedAt'])) {
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

    <!-- Área para exibir os logs de depuração -->
    <div id="debug-log">
        <h3>Debug Logs</h3>
        <pre>
<?php 
if (defined('LOG_FILE') && file_exists(LOG_FILE)) {
    echo htmlspecialchars(file_get_contents(LOG_FILE));
} else {
    echo "Nenhum log encontrado.";
}
?>
        </pre>
    </div>
</body>
</html>
