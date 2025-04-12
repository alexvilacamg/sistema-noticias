<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Utils\Logger.php

namespace App\Utils;

class Logger
{
    /**
     * Registra mensagem de log com nível específico
     * 
     * @param string $message Mensagem a ser registrada
     * @param string $level Nível do log (INFO, WARNING, ERROR, DEBUG)
     * @param string $context Contexto opcional para o log
     * @return bool Sucesso da operação
     */
    public static function log($message, $level = 'INFO', $context = '')
    {
        return debug_log($message, $level, $context);
    }
    
    public static function info($message, $context = '')
    {
        return self::log($message, 'INFO', $context);
    }
    
    public static function warning($message, $context = '')
    {
        return self::log($message, 'WARNING', $context);
    }
    
    public static function error($message, $context = '')
    {
        return self::log($message, 'ERROR', $context);
    }
    
    public static function debug($message, $context = '')
    {
        return self::log($message, 'DEBUG', $context);
    }
}