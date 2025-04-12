<?php
interface NewsScraperInterface {
    /**
     * Método para buscar notícias do portal específico.
     *
     * @return array Array de notícias.
     */
    public function fetchNews(): array;
}
?>
