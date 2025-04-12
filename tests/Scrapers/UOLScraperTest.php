<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\tests\Scrapers\UOLScraperTest.php

namespace Tests\Scrapers;

use App\Models\UOLScraper;
use Tests\TestCase;
use App\Cache\FileCache;

class UOLScraperTest extends TestCase
{
    private $scraper;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar um cache de teste
        $testCache = new FileCache([
            'directory' => __DIR__ . '/../temp/',
            'prefix' => 'test_'
        ]);
        
        // Substituir a função global getCache
        global $testCacheInstance;
        $testCacheInstance = $testCache;
        
        $this->scraper = new UOLScraper();
    }
    
    public function testScrapeArticle()
    {
        // Carregar HTML de teste
        $html = $this->loadTestHtml('uol_sample.html');
        
        // Mock do HttpClient para retornar o HTML de teste
        $this->mockHttpClient($html);
        
        // Usar reflexão para acessar o método privado scrapeArticle
        $reflection = new \ReflectionClass($this->scraper);
        $method = $reflection->getMethod('scrapeArticle');
        $method->setAccessible(true);
        
        // Chamar o método privado
        $result = $method->invokeArgs($this->scraper, ['https://teste.com', []]);
        
        // Verificar os resultados
        $this->assertIsArray($result);
        $this->assertEquals('Este é o primeiro parágrafo da notícia de teste.', $result['description']);
        $this->assertEquals('Maria Santos', $result['author']);
        $this->assertEquals('2025-04-12T11:15:00-03:00', $result['publishedAt']);
    }
    
    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}