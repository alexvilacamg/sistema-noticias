<?php
// background_scrape.php

// Carrega o autoloader do Composer
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Scraper;

$scraper = new Scraper();
$articles = $scraper->getAllPoliticalNews();

$cacheFile = __DIR__ . '/cache/all_news.json';
if (!is_dir(__DIR__ . '/cache')) {
    mkdir(__DIR__ . '/cache', 0777, true);
}

file_put_contents($cacheFile, json_encode($articles));
echo "Scraping concluído com sucesso!";
?>
