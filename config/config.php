<?php
date_default_timezone_set('America/Sao_Paulo');
define('CACHE_DIR', __DIR__ . '/../cache');
define('LOG_DIR', __DIR__ . '/../logs');
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}
define('LOG_FILE', LOG_DIR . '/debug.log');

function debug_log($message) {
    $date = date("Y-m-d H:i:s");
    $entry = "[$date] $message" . PHP_EOL;
    file_put_contents(LOG_FILE, $entry, FILE_APPEND);
}
?>
