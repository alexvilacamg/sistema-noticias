<?php
require_once __DIR__ . '/../models/Scraper.php';

class NewsController {
    public function index() {
        $scraper = new Scraper();
        $news = $scraper->getAllPoliticalNews();
        require_once __DIR__ . '/../views/index.php';
    }
}
?>
