<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Models\NewsScraperInterface.php

namespace App\Models;

interface NewsScraperInterface {
    /**
     * Método para buscar notícias do portal específico.
     *
     * @return array Array de notícias.
     */
    public function fetchNews(): array;
}
?>
