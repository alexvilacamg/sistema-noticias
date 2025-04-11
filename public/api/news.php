<?php
header('Content-Type: application/json');

// Define o caminho para o arquivo de cache
$cacheFile = __DIR__ . '/../../cache/all_news.json';

if (file_exists($cacheFile)) {
    echo file_get_contents($cacheFile);
} else {
    echo json_encode([]);
}
?>
