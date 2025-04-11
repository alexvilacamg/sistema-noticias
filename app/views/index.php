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
                order: [[0, 'desc']], // Ordena por "Publicado em" (a primeira coluna) de forma descendente
                pageLength: 10
            });
            
            // Botão para forçar atualização
            $('#force-update-btn').click(function(){
                $.ajax({
                    url: '/api/force_update.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        alert(response.message);
                        location.reload(); // recarrega a página para mostrar os dados atualizados
                    },
                    error: function(error) {
                        console.error('Erro ao atualizar:', error);
                        alert('Erro ao forçar atualização.');
                    }
                });
            });
        });
    </script>
</head>
<body>
    <h1>Notícias de Política</h1>
    <?php 
        // Exibe informações de atualização do cache
        $cacheFile = __DIR__ . '/../cache/all_news.json';
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
    
    <!-- Botão para forçar atualização -->
    <button id="force-update-btn">Forçar Atualização</button>
    
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

    <!-- Área para exibir os logs de depuração (ordem invertida: do mais recente para o mais antigo) -->
    <div id="debug-log">
        <h3>Debug Logs</h3>
        <pre>
<?php 
if (defined('LOG_FILE') && file_exists(LOG_FILE)) {
    // Lê o arquivo de log como um array de linhas,
    // reverte a ordem e exibe
    $lines = file(LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines);
    echo htmlspecialchars(implode("\n", $lines));
} else {
    echo "Nenhum log encontrado.";
}
?>
        </pre>
    </div>
</body>
</html>
