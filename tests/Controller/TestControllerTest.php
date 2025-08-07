<?php

namespace App\Tests\Controller;

use App\Controller\TestController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Тесты для TestController
 * 
 * Запускаем такой командой:
 * php ./vendor/bin/phpunit --testdox tests/Controller/TestControllerTest.php
 * 
 * @package App\Tests\Controller
 * @author Your Name
 * @since 1.0.0
 */

class TestControllerTest extends TestCase
{
    private TestController $controller;

    protected function setUp(): void
    {
        $this->controller = new TestController();
    }

    /**
     * Тест главной страницы API
     * 
     * Проверяет, что главная страница возвращает корректный JSON ответ
     * с правильной структурой данных.
     * 
     * @return void
     */
    public function testHomePage(): void
    {
        $response = $this->controller->home();
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Добро пожаловать в Symfony API!', $data['message']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('', $data['error']);
    }

    /**
     * Тест тестового эндпоинта
     * 
     * Проверяет работу тестового эндпоинта /api/test
     * 
     * @return void
     */
    public function testTestEndpoint(): void
    {
        $response = $this->controller->test();
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Тестовый эндпоинт работает!', $data['message']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('', $data['error']);
    }

    public function testGetUsers(): void
    {
        $response = $this->controller->getUsers();
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('users', $data['data']);
        $this->assertArrayHasKey('total', $data['data']);
        $this->assertEquals(3, $data['data']['total']);
    }

    public function testCreateUser(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'role' => 'user'
        ];

        // Создаем мок Request объекта
        $request = $this->createMock(Request::class);
        $request->method('getContent')
                ->willReturn(json_encode($userData));

        $response = $this->controller->createUser($request);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Пользователь успешно создан', $data['message']);
        $this->assertArrayHasKey('user', $data['data']);
    }

    public function testHealthCheck(): void
    {
        $response = $this->controller->health();
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('healthy', $data['data']['health_status']);
        $this->assertArrayHasKey('timestamp', $data['data']);
    }
} 