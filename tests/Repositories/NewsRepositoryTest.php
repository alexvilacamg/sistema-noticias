<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\tests\Repositories\NewsRepositoryTest.php

namespace Tests\Repositories;

use App\Factories\RepositoryFactory;
use Tests\TestCase;

class NewsRepositoryTest extends TestCase
{
    protected $repository;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar variável de ambiente para teste
        putenv('DB_DATABASE=sistema_noticias_test');
        
        $this->repository = RepositoryFactory::createNewsRepository();
        
        // Limpar dados de teste existentes
        $this->repository->clear();
    }
    
    public function testSaveAndGet()
    {
        // Criar uma notícia de teste
        $newsItem = [
            'title' => 'Notícia de Teste',
            'url' => 'https://teste.com/noticia-' . uniqid(),
            'description' => 'Descrição de teste',
            'publishedAt' => date('Y-m-d\TH:i:s'),
            'source' => 'G1',
            'author' => 'Autor Teste'
        ];
        
        // Salvar
        $result = $this->repository->save($newsItem);
        $this->assertTrue($result);
        
        // Recuperar todas as notícias
        $allNews = $this->repository->getAll();
        $this->assertCount(1, $allNews);
        $this->assertEquals($newsItem['title'], $allNews[0]['title']);
        
        // Buscar por URL
        $found = $this->repository->findByUrl($newsItem['url']);
        $this->assertNotNull($found);
        $this->assertEquals($newsItem['title'], $found['title']);
    }
    
    public function testClear()
    {
        // Primeiro insere alguns dados
        $this->repository->save([
            'title' => 'Teste 1',
            'url' => 'https://teste.com/1',
            'source' => 'G1'
        ]);
        
        $this->repository->save([
            'title' => 'Teste 2',
            'url' => 'https://teste.com/2',
            'source' => 'UOL'
        ]);
        
        // Verifica que foram inseridos
        $allNews = $this->repository->getAll();
        $this->assertCount(2, $allNews);
        
        // Limpa
        $result = $this->repository->clear();
        $this->assertTrue($result);
        
        // Verifica que foram removidos
        $allNews = $this->repository->getAll();
        $this->assertCount(0, $allNews);
    }
    
    protected function tearDown(): void
    {
        $this->repository->clear();
        parent::tearDown();
    }
}