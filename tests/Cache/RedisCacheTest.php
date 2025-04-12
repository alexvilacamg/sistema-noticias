<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\tests\Cache\RedisCacheTest.php

namespace Tests\Cache;

use App\Cache\RedisCache;
use Tests\TestCase;
use Mockery;
use Predis\Client;

class RedisCacheTest extends TestCase
{
    private $mockRedis;
    private $cache;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar o mock do cliente Redis
        $this->mockRedis = Mockery::mock('Predis\Client');
        
        $this->cache = new class($this->mockRedis) extends RedisCache {
            // Declarar as propriedades explicitamente
            protected $redis;
            protected $prefix;
            protected $defaultTtl;
            private $mockClient;
            
            public function __construct($mockClient)
            {
                $this->mockClient = $mockClient;
                $this->prefix = 'test_';
                $this->defaultTtl = 600;
                // Não inicializa $redis aqui, será feito via reflection
            }
        };
        
        // Injetar o mock diretamente na propriedade $redis usando reflexão
        $reflection = new \ReflectionClass($this->cache);
        $property = $reflection->getProperty('redis');
        $property->setAccessible(true);
        $property->setValue($this->cache, $this->mockRedis);
    }
    
    public function testSetAndGet()
    {
        $key = 'test_key';
        $value = ['name' => 'Test', 'value' => 123];
        $encodedValue = json_encode($value);
        
        // Configurar expectativas do mock
        $this->mockRedis->shouldReceive('setex')
            ->once()
            ->with('test_' . $key, 600, $encodedValue)
            ->andReturn('OK');
            
        $this->mockRedis->shouldReceive('get')
            ->once()
            ->with('test_' . $key)
            ->andReturn($encodedValue);
        
        // Testar o método set
        $this->assertTrue($this->cache->set($key, $value));
        
        // Testar o método get
        $this->assertEquals($value, $this->cache->get($key));
    }
    
    public function testHas()
    {
        $key = 'test_exists';
        
        // Configurar expectativas do mock
        $this->mockRedis->shouldReceive('exists')
            ->once()
            ->with('test_' . $key)
            ->andReturn(1);
        
        // Verificar que o método has funciona
        $this->assertTrue($this->cache->has($key));
    }
    
    public function testDelete()
    {
        $key = 'test_delete';
        
        // Configurar expectativas do mock
        $this->mockRedis->shouldReceive('del')
            ->once()
            ->with('test_' . $key)
            ->andReturn(1);
        
        // Verificar que o método delete funciona
        $this->assertTrue($this->cache->delete($key));
    }
    
    public function testClear()
    {
        // Configurar expectativas do mock para keys e del
        $this->mockRedis->shouldReceive('keys')
            ->once()
            ->with('test_*')
            ->andReturn(['test_key1', 'test_key2']);
            
        $this->mockRedis->shouldReceive('del')
            ->once()
            ->with(['test_key1', 'test_key2'])
            ->andReturn(2);
        
        // Verificar que o método clear funciona
        $this->assertTrue($this->cache->clear());
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

class RedisCache implements CacheInterface
{
    protected $redis;
    protected $prefix;
    protected $defaultTtl;
    // ...
}