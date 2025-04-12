<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\src\App\Factories\RepositoryFactory.php

namespace App\Factories;

use App\Repositories\NewsRepositoryInterface;
use App\Repositories\MysqlNewsRepository;
use App\Repositories\MongoNewsRepository;
use App\Repositories\SqliteNewsRepository;
use App\Utils\Logger;

class RepositoryFactory
{
    public static function createNewsRepository(): NewsRepositoryInterface
    {
        $dbType = getenv('DB_TYPE') ?: 'sqlite';
        
        try {
            switch (strtolower($dbType)) {
                case 'mongodb':
                    return new MongoNewsRepository();
                    
                case 'mysql':
                    return new MysqlNewsRepository();
                    
                case 'sqlite':
                default:
                    return new SqliteNewsRepository();
            }
        } catch (\Exception $e) {
            Logger::error('Error creating repository: ' . $e->getMessage(), 'Factory');
            throw $e;
        }
    }
}