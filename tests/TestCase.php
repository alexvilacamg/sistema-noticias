<?php
// filepath: c:\Users\alexa\OneDrive\Área de Trabalho\sistema-noticias\tests\TestCase.php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Carrega um arquivo HTML de teste
     *
     * @param string $filename Nome do arquivo em tests/TestData
     * @return string Conteúdo do arquivo
     */
    protected function loadTestHtml(string $filename): string
    {
        $path = __DIR__ . '/TestData/' . $filename;
        if (!file_exists($path)) {
            $this->fail("Arquivo de teste não encontrado: $path");
        }
        return file_get_contents($path);
    }
    
    /**
     * Cria um mock para HttpClient para retornar HTML predefinido
     *
     * @param string $htmlContent Conteúdo HTML para retornar
     * @return void
     */
    protected function mockHttpClient(string $htmlContent): void
    {
        // Cria um mock da classe HttpClient usando uma função anônima
        // que substitui o método estático get()
        $mock = \Mockery::mock('alias:App\Utils\HttpClient');
        $mock->shouldReceive('get')
            ->withAnyArgs()
            ->andReturn($htmlContent);
    }
}