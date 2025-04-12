<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Views\Helpers\ViewHelper.php

namespace App\Views\Helpers;

class ViewHelper
{
    /**
     * Formata a data para exibição
     */
    public static function formatDate($date, $format = 'd/m/Y H:i')
    {
        if (empty($date) || $date === "1970-01-01T00:00:00+00:00") {
            return "Data não disponível";
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Trunca um texto para exibição
     */
    public static function truncate($text, $length = 100)
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }
}