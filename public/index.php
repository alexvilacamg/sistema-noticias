<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/App/Controllers/NewsController.php';

$controller = new NewsController();
$controller->index();
?>
