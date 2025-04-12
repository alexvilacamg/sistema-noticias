<?php
// app/views/index.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notícias de Política</title>
    
    <!-- CSS externos -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <!-- CSS do sistema -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/cards.css">
    <link rel="stylesheet" href="/assets/css/table.css">
    <link rel="stylesheet" href="/assets/css/logs.css">
    <link rel="stylesheet" href="/assets/css/dark-mode.css">
    
    <!-- jQuery e DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    
    <!-- JavaScript do sistema -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/dark-mode.js"></script>
    <script src="/assets/js/view-switcher.js"></script>
    <script src="/assets/js/universal-search.js"></script>
    <script src="/assets/js/scraper.js"></script>
    <script src="/assets/js/logs.js"></script>
</head>
<body>
    <!-- Botão de modo escuro -->
    <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Alternar modo escuro">
        <i class="fas fa-moon"></i>
    </button>

    <h1>Notícias de Política</h1>
    <?php 
        require_once __DIR__ . '/../../../config/config.php';
        $cacheFile = CACHE_DIR . '/all_news.json';
        $cacheTime = 600; // 10 minutos
    ?>
    <!-- Update info existente -->
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
    
    <!-- Campo de busca universal -->
    <div class="search-container">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="universal-search" placeholder="Buscar notícias..." class="search-input">
            <button id="clear-search" class="search-clear" title="Limpar busca">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Controles de filtro -->
    <div class="controls-container">
        <div class="source-filters">
            <span class="filter-title">Filtrar por fonte:</span>
            <button class="source-filter active" data-source="all">Todas</button>
            <button class="source-filter" data-source="g1">G1</button>
            <button class="source-filter" data-source="uol">UOL</button>
            <button class="source-filter" data-source="folha">Folha</button>
        </div>
        <div class="view-controls">
            <span class="filter-title">Visualização:</span>
            <button id="grid-view-btn" class="view-btn active" title="Visualização em cards">
                <i class="fas fa-th-large"></i>
            </button>
            <button id="table-view-btn" class="view-btn" title="Visualização em tabela">
                <i class="fas fa-table"></i>
            </button>
        </div>
    </div>

    <!-- Visualizações de notícia -->
    <?php if (isset($news) && is_array($news) && count($news) > 0): ?>
        <!-- Visualização em cards -->
        <div class="news-grid" id="grid-view">
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
                            if (!empty($item['published_at']) && $item['published_at'] !== "1970-01-01T00:00:00+00:00") {
                                echo '<i class="fas fa-calendar-alt"></i> ' . date("d/m/Y H:i", strtotime($item['published_at']));
                            } else {
                                echo '<i class="fas fa-calendar-alt"></i> Data não disponível';
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
        
        <!-- Visualização em tabela -->
        <div id="table-view" style="display: none;">
            <table id="newsTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Título</th>
                        <th>Fonte</th>
                        <th>Autor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $item): ?>
                    <tr>
                        <td data-order="<?php echo isset($item['published_at']) ? $item['published_at'] : ''; ?>">
                            <?php 
                            if (!empty($item['published_at']) && $item['published_at'] !== "1970-01-01T00:00:00+00:00") {
                                echo date("d/m/Y H:i", strtotime($item['published_at']));
                            } else {
                                echo "Data não disponível";
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo $item['url']; ?>" target="_blank" class="news-link">
                                <?php echo $item['title'] ?? 'Sem título'; ?>
                            </a>
                            <?php if (!empty($item['description'])): ?>
                            <div class="description-preview">
                                <?php echo $item['description']; ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="source-badge <?php echo strtolower($item['source']); ?>">
                                <?php echo $item['source']; ?>
                            </span>
                        </td>
                        <td><?php echo $item['author'] ?? 'Não disponível'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Nenhuma notícia encontrada.</p>
    <?php endif; ?>

    <!-- Adicionar link para a área administrativa -->
    <div class="admin-link-container">
        <a href="/admin" class="admin-link">
            <i class="fas fa-cog"></i> Área Administrativa
        </a>
    </div>

    <!-- Scripts JS permanecem os mesmos -->
</body>
</html>
