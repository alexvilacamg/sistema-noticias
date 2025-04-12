<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\tests\Cache\FileCacheTest.php

namespace Tests\Cache;

use App\Cache\FileCache;
use Tests\TestCase;

class FileCacheTest extends TestCase
{
    private $cache;
    private $tempDir;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar diretório temporário para testes
        $this->tempDir = __DIR__ . '/../temp/cache_test_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
        
        $this->cache = new FileCache([
            'directory' => $this->tempDir,
            'prefix' => 'test_',
            'ttl' => 10
        ]);
    }
    
    public function testSetAndGet()
    {
        $key = 'test_key';
        $value = ['name' => 'Test', 'value' => 123];
        
        // Verificar que o valor ainda não existe
        $this->assertNull($this->cache->get($key));
        
        // Armazenar o valor
        $result = $this->cache->set($key, $value);
        $this->assertTrue($result);
        
        // Verificar que o valor foi armazenado corretamente
        $storedValue = $this->cache->get($key);
        $this->assertEquals($value, $storedValue);
    }
    
    public function testHas()
    {
        $key = 'test_exists';
        $value = 'test value';
        
        // Verificar que o valor ainda não existe
        $this->assertFalse($this->cache->has($key));
        
        // Armazenar o valor
        $this->cache->set($key, $value);
        
        // Verificar que o valor existe agora
        $this->assertTrue($this->cache->has($key));
    }
    
    public function testDelete()
    {
        $key = 'test_delete';
        $this->cache->set($key, 'value to delete');
        
        // Verificar que o valor existe
        $this->assertTrue($this->cache->has($key));
        
        // Excluir e verificar
        $result = $this->cache->delete($key);
        $this->assertTrue($result);
        $this->assertFalse($this->cache->has($key));
    }
    
    public function testClear()
    {
        // Adicionar vários itens
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        
        // Verificar que existem
        $this->assertTrue($this->cache->has('key1'));
        $this->assertTrue($this->cache->has('key2'));
        
        // Limpar e verificar
        $result = $this->cache->clear();
        $this->assertTrue($result);
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }
    
    public function testExpiration()
    {
        $key = 'test_expiration';
        
        // Armazenar com TTL de 1 segundo
        $this->cache->set($key, 'expiring soon', 1);
        
        // Verificar que existe imediatamente
        $this->assertTrue($this->cache->has($key));
        
        // Aguardar a expiração
        sleep(2);
        
        // Verificar que expirou
        $this->assertFalse($this->cache->has($key));
        $this->assertNull($this->cache->get($key));
    }
    
    public function testRemember()
    {
        $key = 'test_remember';
        $callCount = 0;
        
        // Função que será chamada apenas uma vez
        $callback = function() use (&$callCount) {
            $callCount++;
            return "Value generated, count: $callCount";
        };
        
        // Primeira chamada - deve executar o callback
        $value1 = $this->cache->remember($key, 10, $callback);
        $this->assertEquals("Value generated, count: 1", $value1);
        $this->assertEquals(1, $callCount);
        
        // Segunda chamada - deve usar o cache
        $value2 = $this->cache->remember($key, 10, $callback);
        $this->assertEquals("Value generated, count: 1", $value2);
        $this->assertEquals(1, $callCount); // o contador ainda é 1
    }
    
    protected function tearDown(): void
    {
        // Remover diretório temporário
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }
    
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
}