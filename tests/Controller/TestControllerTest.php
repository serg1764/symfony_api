<?php

namespace App\Tests\Controller;

use App\Controller\HomeController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Тесты для HomeController
 * 
 * Запускаем такой командой:
 * php ./vendor/bin/phpunit --testdox tests/Controller/TestControllerTest.php
 * 
 * @package App\Tests\Controller
 */
class TestControllerTest extends TestCase
{
    private HomeController $controller;

    protected function setUp(): void
    {
        $this->controller = new HomeController();
    }

    /**
     * Тест главной страницы API
     */
    public function testHomePageReturnsValidJson(): void
    {
        $response = $this->controller->index();
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertStringContainsString('Currency Exchange Rate API', $data['message']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('timestamp', $data);
    }

    /**
     * Тест структуры данных ответа
     */
    public function testResponseStructure(): void
    {
        $response = $this->controller->index();
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('timestamp', $data);
    }

    /**
     * Тест версии API
     */
    public function testApiVersion(): void
    {
        $response = $this->controller->index();
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('1.0.0', $data['data']['version']);
    }

    /**
     * Тест эндпоинтов API
     */
    public function testApiEndpoints(): void
    {
        $response = $this->controller->index();
        
        $data = json_decode($response->getContent(), true);
        $endpoints = $data['data']['endpoints'];
        
        $this->assertArrayHasKey('/api/exchange-rates/{baseCurrency}/{quoteCurrency}', $endpoints);
        $this->assertArrayHasKey('/api/exchange-rates/currencies', $endpoints);
        $this->assertArrayHasKey('/api/exchange-rates/{baseCurrency}/{quoteCurrency}/statistics', $endpoints);
    }

    /**
     * Тест временной метки
     */
    public function testTimestamp(): void
    {
        $response = $this->controller->index();
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertNotEmpty($data['timestamp']);
    }
}
