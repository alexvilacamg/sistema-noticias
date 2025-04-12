<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\tests\Scrapers\G1ScraperTest.php

namespace Tests\Scrapers;

use App\Models\G1Scraper;
use Tests\TestCase;
use App\Cache\FileCache;

class G1ScraperTest extends TestCase
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
        
        // Substituir a função global getCache para retornar nosso cache de teste
        global $testCacheInstance;
        $testCacheInstance = $testCache;
        
        if (!function_exists('getCache')) {
            function getCache() {
                global $testCacheInstance;
                return $testCacheInstance;
            }
        }
        
        $this->scraper = new G1Scraper();
    }
    
    public function testScrapeArticle()
    {
        // Carregar HTML de teste
        $html = $this->loadTestHtml('g1_sample.html');
        
        // Mock do HttpClient para retornar o HTML de teste
        $this->mockHttpClient($html);
        
        // Usar reflexão para acessar o método privado scrapeArticle
        $reflection = new \ReflectionClass($this->scraper);
        $method = $reflection->getMethod('scrapeArticle');
        $method->setAccessible(true);
        
        // Chamar o método privado com os parâmetros necessários
        $result = $method->invokeArgs($this->scraper, ['https://teste.com', []]);
        
        // Verificar se os dados foram extraídos corretamente
        $this->assertIsArray($result);
        $this->assertEquals('Título da notícia de teste G1', $result['title']);
        $this->assertEquals('Descrição de exemplo para teste', $result['description']);
        $this->assertEquals('João Silva', $result['author']);
        $this->assertEquals('2025-04-12T10:30:00-03:00', $result['publishedAt']);
        $this->assertEquals('G1', $result['source']);
    }
    
    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}