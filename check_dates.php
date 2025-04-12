<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\check_dates.php

require_once __DIR__ . '/vendor/autoload.php';

try {
    // Conectar ao SQLite
    $db = new PDO("sqlite:" . __DIR__ . '/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar e mostrar as datas
    $stmt = $db->query("SELECT title, published_at FROM news LIMIT 20");
    
    echo "FORMATO DAS DATAS NO BANCO:\n";
    echo "====================================\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Título: " . substr($row['title'], 0, 30) . "...\n";
        echo "Data Raw: [" . $row['published_at'] . "]\n";
        echo "strtotime: " . (strtotime($row['published_at']) ? 'OK' : 'FALHA') . "\n";
        echo "====================================\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}