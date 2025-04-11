<?php
// Inicia o buffer e ativa exibição de erros (para depuração - remova display_errors depois)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

ob_start();
header('Content-Type: application/json');

// Inclua as configurações globais (certifique-se de que o config/config.php NÃO tenha saída extra)
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Scraper.php';

$scraper = new Scraper();
$articles = $scraper->getAllPoliticalNews(true); // Força atualização

$cacheDir = __DIR__ . '/../../cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}
$cacheFile = $cacheDir . '/all_news.json';
file_put_contents($cacheFile, json_encode($articles));

$response = [
    'status' => 'success',
    'message' => 'Atualização forçada realizada com sucesso!',
    'articles_count' => count($articles)
];

// Limpa qualquer saída que possa ter sido gerada antes do JSON
ob_end_clean();
echo json_encode($response);
?>
