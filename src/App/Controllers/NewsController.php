<?php
require_once __DIR__ . '/../Models/Scraper.php';

class NewsController {
    public function index() {
        $scraper = new Scraper();
        $news = $scraper->getAllPoliticalNews();
        require_once __DIR__ . '/../Views/index.php';
    }
}
?>
