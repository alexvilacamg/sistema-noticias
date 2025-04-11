<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/NewsController.php';

$controller = new NewsController();
$controller->index();
?>
